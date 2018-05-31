<?php

class Users implements SplSubject
{
    public $observers = [];

    public function attach(SplObserver $observer)
    {
        array_push($this->observers, $observer);
    }

    public function detach(SplObserver $observer)
    {
        $index = array_search($observer, $this->observers);
        if ($index === false) {
            return false;
        } else {
            unset($this->observers[$index]);
            return true;
        }
    }

    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function register()
    {
        $this->notify();
    }

    public function sendMail()
    {
        echo "send mail";
    }
}

class EmailServer implements SplObserver
{
    public function update(SplSubject $subject)
    {
        $subject->sendMail();
    }
}

$user = new Users();
$user->attach(new EmailServer());

$user->register();

