<?php
namespace Seeker\Manager;

use Seeker\Server\Tcp\Worker as TcpWorker;
use Seeker\Service\Dispatcher;
use Seeker\Server\Connection;

class Worker extends TcpWorker
{

    protected $dispatcher = null;
    protected $nodes = [];
    protected $clients = [];

    public function onStart()
    {
        //启动。。注册协议监听。。
        $this->dispatcher = new Dispatcher($this);
        $this->dispatcher->listens([
            'common.node.login' => [
                'service' => 'Seeker\\Service\\Common\\Node:login',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
            'tool.deploy.push' => [
                'service' => 'Seeker\\Manager\\Service\\Deploy:push',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
            'common.event_listen.listen' => [
                'service' => 'Seeker\\Service\\Common\\EventListen:listen',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
            'common.event_listen.remove' => [
                'service' => 'Seeker\\Service\\Common\\EventListen:remove',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ]
        ]);

        $this->dispatcher->remoteCalls([
            'manager.deploy.push' => [
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ]
        ]);

        $this->dispatcher->listenEvents([
            'event:node.deploy.push' => [
                'service' => 'Seeker\\Service\\Common\\Node:login',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ]
        ]);

        $this->dispatcher->productEvents([
            'event:node.deploy.push' => [
                'request' => 'Seeker\\Protocol\\Json'
            ]
        ]);

        //开始连接Nodes......
        $nodeClient = new NodeClient('0.0.0.0', 9902);
        $nodeClient
            ->setDispatcher($this->dispatcher)
            ->setNodeId(10000)
            ->setAuthKey('node_10000')
            ->connect();
    }

    public function onReceive($id, $data)
    {
        $this->dispatcher->dispatch($this->clients[$id], $data);
    }

    public function onConnect($id)
    {
        $this->clients[$id] = new Connection($id, $this);
        echo 'onConnect....' . PHP_EOL;
    }
}