<?php
namespace Test;

use Seeker\Service\Process as ServiceProcess;
use Swoole\Timer;

class Process extends ServiceProcess
{
    public function onStart()
    {
        parent::onStart();
        Timer::tick(1000, [$this, 'sendTest']);
    }

    public function sendTest()
    {
        $this->dispatcher->remoteCall('user.common.login')
            ->then(function($connection, $response) {
                \Console::debug('user.common.login code: ' . $response->getCode());
            })
            ->sendTo($this->connection);
    }
}