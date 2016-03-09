<?php

namespace Seeker\Server\Tcp;

use Seeker\Server\Base;

use Seeker\Server\Standard\ListenerInterface;
use Seeker\Server\Standard\ListenerBaseTrait;

class Listener implements ListenerInterface
{
    use ListenerBaseTrait;

    public function __construct($port = 9901, $host = '127.0.0.1')
    {
        $this->port = $port;
        $this->host = $host;
    }

    public function setWorker(Worker $worker)
    {
        $this->worker = $worker;
    }

    public function getType()
    {
        return Base::TYPE_SOCK_TCP;
    }
}