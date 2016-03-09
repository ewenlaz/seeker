<?php
namespace Seeker\Node;

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
            'manager.deploy.push' => [
                'service' => 'Seeker\\Node\\Service\\Deploy:push',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ]
        ]);
        
        $this->dispatcher->remoteCalls([
            'node.deploy.progress' => [
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ]
        ]);
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