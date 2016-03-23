<?php
namespace Test;

use Seeker\Service\Process as ServiceProcess;
use Swoole\Timer;

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


class Process extends ServiceProcess
{
    public $sendIndex = 0;
    public $recv = 0;

    public function onStart()
    {
        parent::onStart();
        Timer::tick(10000, [$this, 'sendStat']);

    }

    public function sendStat()
    {
        if (!$this->sendIndex) {
            $this->sendIndex = 1;
            $this->startTime = time() - 1;
            $this->startMem = memory_get_usage();
            Timer::tick(100, [$this, 'sendTest']);
        }
        $time = time();
        $avg = intval($this->recv/($time - $this->startTime));
        $mem = memory_get_usage() - $this->startMem;
        $mem = convert($mem);
        \Console::info('send:%d, recv:%d, avg:%d, mem:%s', $this->sendIndex, $this->recv, $avg, $mem);
    }

    public function sendTest()
    {
        for ($i = 0; $i < 1; $i ++) {
            $this->sendIndex ++;
            $this->dispatcher->remoteCall('user.common.login')
                ->then([$this, 'onStat'])
                ->sendTo($this->connection);
        }
    }

    public function onStat()
    {
        $this->recv ++;
    }
}