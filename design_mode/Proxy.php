<?php

require_once '../utils.php';

# 代理模式

interface IGamePlayer
{
    public function login($name, $password);

    public function killBoss();

    public function upGrade();
}

class GamePlayer implements IGamePlayer
{
    protected $name;

    protected $proxy;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function login($name, $password)
    {
        echoLine('用户' . $name . '登录了');
    }

    public function killBoss()
    {
        echoLine('杀敌了');
    }

    public function upGrade()
    {
        echoLine('升级成功');
    }

    public function setProxy(IGamePlayer $proxy)
    {
        $this->proxy = $proxy;
    }

    public function isProxy()
    {
        return !!$this->proxy;
    }
}


class GamePlayerProxy implements IGamePlayer
{
    protected $game_player;

    public function __construct(IGamePlayer $game_player)
    {
        $this->game_player = $game_player;
    }

    public function login($name, $password)
    {
        $this->game_player->login($name, $password);
    }

    public function killBoss()
    {
        $this->game_player->killBoss();
    }

    public function upGrade()
    {
        $this->game_player->upGrade();
    }
}


# 动态代理
class DynamicProxy
{
    protected $subject;

    public function __construct(IGamePlayer $subject)
    {
        $this->subject = $subject;
    }

    public function __call($name, $arguments)
    {
        # 过滤操作
        if (method_exists($this->subject, 'beforeAction')) {
            if ($this->subject->beforeAction()) {
                call_user_func_array([$this->subject, $name], $arguments);
                method_exists($this->subject, 'afterAction') && $this->subject->afterAction();
            }
        }
    }
}