<?php

# 房间信息

namespace games\jump\models;

use base\Model;
use base\structs\RedisCollection;
use base\structs\RedisData;
use base\structs\RedisQueue;

use games\jump\Application;


/**
 * id   房间id
 * room_id  房间id
 * status      房间状态
 * online_num    房间在线用户数
 * game_user_num    房间游戏用户数
 * timestamp    时间戳
 * loop_num     回合数
 */
class Rooms extends Model
{
    public $user_collection;
    public $watch_user_collection;
    public $control_data;
    public $start_queue;

    const QUEUE_DELIMITER = -1;

    public function __construct($id)
    {
        parent::__construct($id);
        $this->user_collection = $this->getUserCollection($id);
        $this->control_data = $this->getControlData($id);
        $this->watch_user_collection = $this->getWatchUserCollection($id);
        $this->start_queue = $this->getStartQueue($id);
    }

    public function isWait()
    {
        $status = $this->get('status');
        return $status == ROOM_STATUS_WAIT;
    }

    public function isStart()
    {
        $status = $this->get('status');
        return $status == ROOM_STATUS_START;
    }

    public function isEnd()
    {
        $status = $this->get('status');
        return $status == ROOM_STATUS_END;
    }

    public function getDataHashKey($id)
    {
        return Application::name() . '_room_hash_' . $id;
    }

    protected function getControlDataKey($room_id)
    {
        return Application::name() . '_room_control_data_' . $room_id;
    }

    protected function getUserCollectionKey($room_id)
    {
        return Application::name() . '_room_user_collection_' . $room_id;
    }

    protected function getWatchUserCollectionKey($room_id)
    {
        return Application::name() . '_room_watch_user_collection_' . $room_id;
    }

    protected function getStartQueueKey($room_id)
    {
        return Application::name() . '_room_start_queue_' . $room_id;
    }

    protected function getControlData($room_id)
    {
        $room_control_data_key = $this->getControlDataKey($room_id);
        return new RedisData($room_control_data_key);
    }

    protected function getUserCollection($room_id)
    {
        $user_collection_key = $this->getUserCollectionKey($room_id);
        return new RedisCollection($user_collection_key);
    }

    protected function getWatchUserCollection($room_id)
    {
        $watch_user_collection_key = $this->getWatchUserCollectionKey($room_id);
        return new RedisCollection($watch_user_collection_key);
    }

    protected function getStartQueue($room_id)
    {
        $start_queue_key = $this->getStartQueueKey($room_id);
        return new RedisQueue($start_queue_key);
    }

    public function containUser(Users $user)
    {
        $user_ids = $this->getAllUserIds();
        return in_array($user->id, $user_ids);
    }

    public function containUserId($user_id)
    {
        $user_ids = $this->getAllUserIds();
        return in_array($user_id, $user_ids);
    }

    public function addUser(Users $user)
    {
        $this->user_collection->add($user->id);
        $this->incr('game_user_num');
        $this->incr('online_num');
    }

    public function removeUser(Users $user)
    {
        $this->user_collection->remove($user->id);
        $this->decr('game_user_num');
        $this->decr('online_num');
    }

    public function addWatchUser(Users $user)
    {
        $this->watch_user_collection->add($user->id);
    }

    public function removeWatchUser(Users $user)
    {
        $this->watch_user_collection->remove($user->id);
    }

    public function getUserIds()
    {
        return $this->user_collection->getAll();
    }

    public function getWatchUserIds()
    {
        return $this->watch_user_collection->getAll();
    }

    public function getAllUserIds()
    {
        $user_ids = $this->getUserIds();
        $watch_user_ids = $this->getWatchUserIds();
        return array_unique(array_merge($user_ids, $watch_user_ids));
    }

    public function getUsers()
    {
        $user_ids = $this->getUserIds();
        $users = [];
        foreach ($user_ids as $user_id) {
            $user = new Users($user_id);
            $detail = $user->detail();
            if ($detail) {
                $users[] = $user->detail();
            }
        }
        return $users;
    }

    public function getUsersByStatus($status)
    {
        $users = $this->getUsers();
        $data = [];
        foreach ($users as $user) {
            if ($user['status'] == $status) {
                array_push($data, $user);
            }
        }
        return $data;
    }

    public function getWatchUsers()
    {
        $watch_user_ids = $this->getWatchUserIds();
        $users = [];
        foreach ($watch_user_ids as $user_id) {
            $user = new Users($user_id);
            $detail = $user->detail();
            if ($detail) {
                $users[] = $user->detail();
            }
        }
        return $users;
    }

    # 排行榜
    public function getRankResult()
    {
        $users = $this->getUsersByStatus(ROOM_USER_STATUS_END);
        return rectSort($users, 'score desc');
    }

    # 获取房间的所有用户
    public function getAllUsers()
    {
        $ids = $this->getAllUserIds();
        $users = [];
        foreach ($ids as $id) {
            $user = new Users($id);
            $detail = $user->detail();
            if ($detail) {
                $users[] = $user->detail();
            }
        }
        return $users;
    }

    # 获取游戏下一个用户id
    public function getNextUserId()
    {
        $user_id = $this->start_queue->next();
        if ($user_id == self::QUEUE_DELIMITER) {
            $this->incr('loop_num');
            $user_id = $this->start_queue->next();
            if ($user_id == self::QUEUE_DELIMITER) {
                return null;
            }
        }
        return $user_id;
    }

    # 获取游戏下一个用户详细信息
    public function getNextUser()
    {
        $next_user_id = $this->getNextUserId();
        if ($next_user_id) {
            $user = new Users($next_user_id);
            $this->setCurrentUser($user);
            return $user->detail();
        }
        return null;
    }

    public function getCurrentUser()
    {
        $user_id = $this->get('current_user_id');
        $user = Users::findById($user_id);
        if($user){
            return $user->detail();
        }else{
            return [];
        }
    }

    public function setCurrentUser($user)
    {
        $this->set('current_user_id', $user->id);
    }

    # 设置游戏房间控制信息
    public function setControlDataInfo($data)
    {
        $json = json_encode($data);
        $this->control_data->set($json);
    }

    # 获取游戏房间控制信息
    public function getControlDataInfo()
    {
        $json = $this->control_data->get();
        return json_decode($json);
    }

    # 房间信息初始化
    public function init($data)
    {
        $this->destroy();
        $this->setData($data);
    }

    # 房间销毁
    public function destroy()
    {
        $users = $this->getAllUsers();
        foreach ($users as $user) {
            $model = Users::findById($user['user_id']);
            $model->delete();
        }
        parent::delete();
        $this->user_collection->delete();
        $this->watch_user_collection->delete();
        $this->start_queue->delete();
        $this->control_data->delete();
    }

    # 房间信息重置
    public function reset()
    {
        $this->setData(['loop_num' => 0, 'next_user_id' => 0, 'status' => ROOM_STATUS_WAIT]);
        $users = $this->getUsers();
        foreach ($users as $user) {
            $model = Users::findById($user['user_id']);
            $model->reset();
        }
    }

    # 更新时间戳
    public function updateTimestamp()
    {
        $this->set('timestamp', time());
    }

    # 关闭房间
    public function closeRoom()
    {
        $this->destroy();
    }

    # 结束游戏
    public function endGame()
    {
        $this->set('status', ROOM_STATUS_END);
        $users = $this->getUsers();
        foreach ($users as $user) {
            $model = Users::findById($user['user_id']);
            if ($model) {
                $model->set('status', ROOM_USER_STATUS_END);
            }
        }
    }

    # 开始游戏
    public function startGame()
    {
        $this->set('status', ROOM_STATUS_START);
        $users = $this->getUsers();
        $this->start_queue->delete();
        $this->start_queue->push(self::QUEUE_DELIMITER);
        foreach ($users as $user) {
            $model = Users::findById($user['user_id']);
            if ($model) {
                $this->start_queue->push($model->id);
                $model->set('status', ROOM_STATUS_START);
            }
        }
    }
}