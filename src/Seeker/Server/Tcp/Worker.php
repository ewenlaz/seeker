<?php
namespace Seeker\Server\Tcp;

use Seeker\Server\Standard\WorkerInterface;
use Seeker\Server\Standard\WorkerBaseTrait;

class Worker implements WorkerInterface, TcpWorkerInterface
{
    use WorkerBaseTrait;

    public function onConnect($connection)
    {

    }

    public function onClose($connection)
    {

    }

    public function onReceive($connection, $data)
    {

    }

    public function onStart()
    {

    }

    public function onStop()
    {

    }
}