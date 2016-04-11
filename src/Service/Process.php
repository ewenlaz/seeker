<?php

namespace Seeker\Service;

use Seeker\Message\ServiceBus;
use Seeker\Proto\Wrapper;
use Seeker\Node\Client;

class Process
{
    protected $node = null;
    public function __construct()
    {
        $this->messageBus = new ServiceBus;
        $this->messageBus->protoManager
            ->registerService('service.process.connect', 'Seeker\\Proto\\Blank', 'Seeker\\Proto\\Blank', 'Seeker\\Service\\Common\\Process:connect');

        $this->messageBus->protoManager
            ->registerCall('common.node.login', 'Seeker\\Proto\\Json', 'Seeker\\Proto\\Json');

        $this->messageBus->protoManager
            ->registerCall('common.node.service_sync', 'Seeker\\Proto\\Json', 'Seeker\\Proto\\Json');

        $connection = new Client(SOCKET_FILE);
        $connection->listenStatus([$this, 'onClientStatus']);
        $connection->listenReceive([$this->messageBus, 'onReceive']);
        $connection->connect();
    }

    public function onClientStatus($connection, $status = 0)
    {
        echo 'xxxxx' . PHP_EOL;
        switch ($status) {
            case Client::STATUS_CONNECT:
                //发送协议。
                //模拟一条通知协议...
                $proto = new Wrapper(null, 'service.process.connect');
                $connection->onReceive($proto->toStream());
            break;
        }
    }
}