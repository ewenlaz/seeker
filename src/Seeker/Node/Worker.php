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
            'node.deploy.push' => [
                'service' => 'Seeker\\Node\\Service\\Deploy:push',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_MANAGER
            ],
            //进程管理部分 , 客房端调用
            'node.deploy.start_process' => [
                'service' => 'Seeker\\Manager\\Service\\Deploy:startProcess',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_MANAGER
            ],

            'node.deploy.stop_process' => [
                'service' => 'Seeker\\Manager\\Service\\Deploy:stopProcess',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_MANAGER
            ],

            'node.deploy.remove_process' => [
                'service' => 'Seeker\\Manager\\Service\\Deploy:removeProcess',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_MANAGER
            ],
            //Service部分.. , 客房端调用
            'node.deploy.start_service' => [
                'service' => 'Seeker\\Manager\\Service\\Deploy:startService',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_MANAGER
            ],
            'node.deploy.stop_service' => [
                'service' => 'Seeker\\Manager\\Service\\Deploy:stopService',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_MANAGER
            ],
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