<?php

# 用户信息

namespace games\jump\models;

use base\Model;
use games\jump\Application;


/**
 * id
 * user_id
 * room_id
 * owner
 * loop_num
 * username
 * avater_url
 * site
 * code
 * status
 * score
 * updated_at
 */
class Users extends Model
{
    public function getDataHashKey($id)
    {
        return Application::name() . '_room_user_hash_id_' . $id;
    }

    public function enterRoom(Rooms $room)
    {
        $this->set('room_id', $room->id);
        if ($this->isSite()) {
            $room->addUser($this);
        } else {
            $this->set('status', ROOM_USER_STATUS_WATCH);
            $room->addWatchUser($this);
        }
    }

    public function exitRoom()
    {
        $room = $this->getRoom();
        if ($room) {
            if ($this->isWatch()) {
                $room->removeWatchUser($this);
            } else {
                $room->removeUser($this);
            }
        }
        $this->delete();
    }

    public function isOwner()
    {
        $owner = $this->get('owner');
        return $owner == ROOM_OWNER_STATUS_TRUE;
    }

    public function isSite()
    {
        $site = $this->get('site');
        return $site > 0;
    }

    public function isWait()
    {
        $status = $this->get('status');
        return $status == ROOM_USER_STATUS_WAIT;
    }

    public function isStart()
    {
        $status = $this->get('status');
        return $status == ROOM_USER_STATUS_START;
    }

    public function isEnd()
    {
        $status = $this->get('status');
        return $status == ROOM_USER_STATUS_END;
    }

    public function isWatch()
    {
        $status = $this->get('status');
        return $status == ROOM_USER_STATUS_WATCH;
    }

    # 初始化用户信息
    public function init($data)
    {
        # 用户信息初始化
        $data = [
            'user_id' => $this->id,
            'user_fd' => fetch($data, 'user_fd'),
            'room_id' => fetch($data, 'room_id'),
            'sid' => fetch($data, 'sid'),
            'owner' => fetch($data, 'owner'),
            'username' => fetch($data, 'username'),
            'avater_url' => fetch($data, 'avater_url'),
            'site' => fetch($data, 'site'),
            'status' => ROOM_USER_STATUS_WAIT,
            'score' => 0,
            'loop_num' => 0,
            'updated_at' => time()
        ];
        info('用户初始信息', $data);
        $this->setData($data);
    }

    # 重置用户状态
    public function reset()
    {
        $this->setData(['score' => 0, 'updated_at' => time(), 'loop_num' => 0, 'status' => ROOM_USER_STATUS_WAIT]);
    }

    # 获取用户所在房间对象
    public function getRoom()
    {
        $room_id = $this->get('room_id');
        info('用户获取房间', $room_id);
        if ($room_id) {
            return Rooms::findById($room_id);
        } else {
            return null;
        }
    }

    # 指定用户游戏失败
    public static function gameOverByUserId($user_id)
    {
        $user = new static($user_id);
        $room = $user->getRoom();
        if ($room) {
            info('用户', $user_id, '游戏失败,移除游戏队列');
            $room_loop_num = $room->get('loop_num');
            $user->setData(['loop_num' => $room_loop_num, 'status' => ROOM_USER_STATUS_END]);
            $room->start_queue->remove($user_id);
        }
        return $user->detail();
    }

    # 当前用户游戏失败
    public function gameOver()
    {
        return static::gameOverByUserId($this->id);
    }


    # 更新用户分数
    public function updateScore($score)
    {
        $this->setData(['updated_at' => time(), 'score' => $score]);
    }
}