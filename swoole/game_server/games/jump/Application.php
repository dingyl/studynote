<?php

namespace games\jump;

use base\AbstractApplication;

use base\utils\MsgBuilder;

use games\jump\models\Users;
use games\jump\models\Rooms;

Class Application extends AbstractApplication
{
    const LIMIT_TIME = 20;

    const ERROR_CODE_USER_NOT_EXIST = 1;    # 用户不存在
    const ERROR_CODE_ROOM_NOT_EXIST = 2;    # 房间不存在
    const ERROR_CODE_DENY = 3;    # 没有权限
    const ERROR_CODE_LOGIN_AGAIN = 4;   # 再次登录被挤掉

    public static $ERROR_CODE_MAP = [
        self::ERROR_CODE_USER_NOT_EXIST => '用户不存在',
        self::ERROR_CODE_ROOM_NOT_EXIST => '房间不存在',
        self::ERROR_CODE_DENY => '没有权限',
        self::ERROR_CODE_LOGIN_AGAIN => '再次登录被挤掉',
    ];

    public static $UN_CHECK_ACTION = ['enter_room'];

    public function unNeedCheckAction($action_name)
    {
        return in_array($action_name, self::$UN_CHECK_ACTION);
    }

    public static function name()
    {
        return 'jump';
    }

    public function beforeAction()
    {
        $user = $this->getUser();
        if (!$user) {
            $this->error_reason = '用户不存在';
            info('前置检验', $this->error_reason);
            return false;
        } else {
            $room = $user->getRoom();
            if (!$room) {
                $this->error_reason = '用户房间不存在';
                info('前置检验', $this->error_reason);
                return false;
            }
        }
        return true;
    }

    public function afterOpen()
    {
        # 初始转换信息
        $data = $this->connect->detail();
        $user_id = intval($data['sid']);
        $this->connect->set('user_id', $user_id);

        $this->enterRoom();
    }

    public function beforeClose()
    {
        $this->leaveRoom();
    }


    protected function notifyClient($sid, $type)
    {
        $user = $this->getUser();
        if ($user) {
            $request_info = $this->connect->detail();
            $code = fetch($request_info, 'code');
            $room_id = fetch($request_info, 'room_id');
            $game_history_id = fetch($request_info, 'game_history_id');
            $body = [
                'sid' => $sid,
                'code' => $code,
                'type' => $type,
                'room_id' => $room_id,
                'game_history_id' => $game_history_id,
            ];
            $url = \BaseModel::config('client_notify_url');
            httpGet($url, $body);
            info('通知客户端房间消息 url', $url, '消息体', $body);
        }
    }

    protected function sendToRoom(Rooms $room, $action, $data = [])
    {
        $users = $room->getAllUsers();
        $server = $this->connect->server;
        $msg_json = MsgBuilder::buildJson($action, $data);
        info('广播 房间信息', $room->detail(), '广播信息', $msg_json, '房间用户信息', $users);
        foreach ($users as $user) {
            if (isset($user['user_fd']) && $server->exist($user['user_fd'])) {
                $server->push($user['user_fd'], $msg_json);
            }
        }
    }

    protected function reback($action, $data = [])
    {
        $msg_json = MsgBuilder::buildJson($action, $data);
        $connect = $this->connect;
        info('回复消息', $msg_json);
        if ($connect->server->exist($connect->id)) {
            $connect->server->push($connect->id, $msg_json);
        }
    }

    protected function sendToUser(Users $user, $action, $data = [])
    {
        $server = $this->connect->server;
        $msg_json = MsgBuilder::buildJson($action, $data);
        $server->push($user->get('user_fd'), $msg_json);
    }

    protected function getUser()
    {
        $user_id = $this->connect->get('user_id');
        return Users::findById($user_id);
    }


    # 进入房间
    public function enterRoom()
    {
        $room_id = $this->connect->get('room_id');
        $user_id = $this->connect->get('user_id');

        $detail = $this->connect->detail();

        # 用户
        $user = Users::findById($user_id);
        if ($user) {
            info('关闭已有用户连接', $user->detail());
            $error_data = MsgBuilder::buildError(self::$ERROR_CODE_MAP, self::ERROR_CODE_LOGIN_AGAIN);
            $detail['user_fd'] = $this->connect->id;
            $this->sendToUser($user, 'error', $error_data);
            $this->connect->server->close($user->user_fd);
        } else {
            $user = new Users($user_id);
        }
        $detail['user_fd'] = $this->connect->id;
        $user->init($detail);

        # 房主
        $room = Rooms::findById($room_id);
        if ($room) {
            $user->enterRoom($room);
        } else {
            if ($user->isOwner()) {
                $room = new Rooms($room_id);
                $data = [
                    'id' => $room_id,
                    'room_id' => $room_id,
                    'status' => ROOM_STATUS_WAIT,
                    'online_num' => 0,
                    'game_user_num' => 0,
                    'owner_id' => $user->id,
                    'owner_sid' => $user->sid,
                    'timestamp' => time(),
                    'loop_num' => 0
                ];
                info('初始化房间信息', $room->detail());
                $room->init($data);
                $user->enterRoom($room);
                $this->notifyClient($user->sid, 'wait');
            } else {
                $error_data = MsgBuilder::buildError(self::$ERROR_CODE_MAP, self::ERROR_CODE_ROOM_NOT_EXIST);
                $this->sendToUser($user, 'error', $error_data);
                return;
            }
        }

        # 游戏等待中
        if ($room->isWait()) {
            $base_info = $room->detail();
            $users = $room->getUsersByStatus(ROOM_USER_STATUS_WAIT);
            $base_info['users'] = $users;
            $this->sendToRoom($room, 'waiting', $base_info);
        }

        # 游戏进行中
        if ($room->isStart()) {

            # 获取当前正在进行中的用户
            $next_user = $room->getCurrentUser();
            if($next_user['status'] != ROOM_USER_STATUS_START){
                $next_user = [];
            }
            $users = $room->getUsersByStatus(ROOM_USER_STATUS_START);
            $base_info = $room->detail();
            $base_info['control_data'] = $room->getControlDataInfo();
            $base_info['next_user'] = $next_user;
            $base_info['users'] = $users;
            $this->reback('start', $base_info);
            $data = [
                'users' => $users,
                'next_user' => $next_user,
                'loser' => '',
            ];
            $this->reback('next', $data);
        }

        # 游戏结束
        if ($room->isEnd()) {
            $rank_result = $room->getRankResult();
            $this->reback('end', ['users' => $rank_result]);
        }
    }


    # 离开房间
    public function leaveRoom()
    {
        $user_id = $this->connect->get('user_id');
        $fd = $this->connect->id;
        $user = Users::findById($user_id);
        if ($user) {
            # 当前退出的用户fd和当前连接的fd一致
            if ($user->user_fd == $fd) {
                $room = $user->getRoom();

                if ($room) {

                    if ($user->isOwner() && !$room->isStart()) {
                        $user->exitRoom();
                        $this->sendToRoom($room, 'close_room');
                        $this->notifyClient($user->sid, 'over');
                        $room->closeRoom();
                    }

                    if ($room->isStart() && $user->isStart()) {
                        $user->gameOver();
                    }

                    if ($room->isWait() && $user->isSite()) {
                        $user->exitRoom();
                        $base_info = $room->detail();
                        $users = $room->getUsersByStatus(ROOM_USER_STATUS_WAIT);
                        $base_info['users'] = $users;
                        $this->sendToRoom($room, 'waiting', $base_info);
                    } else {
                        $user->exitRoom();
                    }
                }


            }
            info('用户断开socket连接', $user->detail());
        }
    }

    # 开始游戏
    public function start($control_data)
    {
        $user = $this->getUser();
        $room = $user->getRoom();
        if ($user && $room) {
            $room->updateTimestamp();
            if ($user->isOwner()) {
                $room->startGame();
                $room->setControlDataInfo($control_data);
                $next_user = $room->getNextUser();
                $data = [
                    'control_data' => $control_data,
                    'next_user' => $next_user,
                ];
                $this->sendToRoom($room, 'start', $data);
                $this->notifyClient($room->owner_sid, 'start');
                $this->deleyVerifyUser($next_user);
            } else {
                $error_data = MsgBuilder::buildError(self::$ERROR_CODE_MAP, self::ERROR_CODE_DENY);
                $this->reback('error', $error_data);
            }
        }
    }


    # 更新分数
    public function updateScore($data)
    {
        $user = $this->getUser();
        if ($user && isset($data['score'])) {
            $user->updateScore($data['score']);
            $room = $user->getRoom();
            if ($room) {
                $room->updateTimestamp();
                $this->nextStep();
            }
        }
    }

    # 再来一局游戏
    public function restart()
    {
        $user = $this->getUser();
        $room = $user->getRoom();
        $room->updateTimestamp();
        if ($user->isOwner()) {
            info('房主', $user->id, '发起再来一局');
            $room->reset();
            $base_info = $room->detail();
            $users = $room->getUsersByStatus(ROOM_USER_STATUS_WAIT);
            $base_info['users'] = $users;
            $this->sendToRoom($room, 'restart', $base_info);
        } else {
            $error_data = MsgBuilder::buildError(self::$ERROR_CODE_MAP, self::ERROR_CODE_DENY);
            $this->reback('error', $error_data);
        }
    }

    # 游戏一轮结束
    public function fail()
    {
        info('房主主动结束游戏');
        $user = $this->getUser();
        $room = $user->getRoom();
        $owner_id = $room->owner_id;

        $room->updateTimestamp();
        $room->endGame();
        $rank_result = $room->getRankResult();
        $this->sendToRoom($room, 'end', ['users' => $rank_result]);

        # 房间离开房间
        if (!$room->containUserId($owner_id)) {
            if ($rank_result && isset($rank_result[0])) {
                $first_user = $rank_result[0];
                $this->notifyClient($first_user['sid'], 'over');
            }

            $application = $this;
            # 延迟一秒广播关闭房间
            swoole_timer_after(1000, function () use ($application, $room) {
                $this->sendToRoom($room, 'close_room');
                $room->closeRoom();
            });
        }
    }

    # 客户控制请求  直接回应
    public function control($data)
    {
        $user = $this->getUser();
        $room = $user->getRoom();
        $room->setControlDataInfo($data);
        $this->sendToRoom($room, 'control', $data);
    }

    # 游戏中的用户变成观战用户
    public function watch()
    {
        $user = $this->getUser();
        $loser = $user->gameOver();
        $this->nextStep($loser);
    }


    protected function nextStep($loser = '')
    {
        $user = $this->getUser();
        if ($user) {
            $room = $user->getRoom();
            if ($room) {
                $room->updateTimestamp();
                $next_user = $room->getNextUser();
                if ($next_user) {
                    info('下一个用户信息', $next_user);
                    $data = [
                        'next_user' => $next_user,
                        'users' => $room->getUsersByStatus(ROOM_USER_STATUS_START),
                        'loser' => $loser
                    ];
                    $this->sendToRoom($room, 'next', $data);
                    $this->deleyVerifyUser($next_user);
                } else {
                    info("没有用户了，游戏结束");
                    $this->fail();
                }
            }
        }
    }

    # 延迟验证用户是否已经操作
    protected function deleyVerifyUser($next_user)
    {
        $user_id = $next_user['user_id'];
        $user = Users::findById($user_id);
        $room = $user->getRoom();
        if ($user && $room) {
            $room_timestamp = $room->get('timestamp');
            swoole_timer_after(Application::LIMIT_TIME * 1000, function () use ($room_timestamp, $next_user, $user, $room) {
                $current_room_timestamp = $room->get('timestamp');
                $updated_at = $user->get('updated_at');
                if ($current_room_timestamp && $current_room_timestamp == $room_timestamp) {
                    $user = Users::findById($next_user['user_id']);

                    # 用户已经不存在了
                    if ($user) {
                        if (!$user->isStart()) {
                            info('延迟检查,用户重新进入，游戏失败');
                            $this->nextStep($next_user);
                        }

                        if ($user->isStart() && $updated_at + Application::LIMIT_TIME < time()) {
                            info('延迟检查,用户未操作，游戏失败');
                            $user->gameOver();
                            $this->nextStep($next_user);
                        }
                    } else {
                        info('延迟检查,用户已经离开房间了，游戏失败');
                        $this->nextStep($next_user);
                    }
                }
            });
        }
    }
}