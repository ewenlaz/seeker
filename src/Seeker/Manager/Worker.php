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
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_GUEST
            ],
            'common.event_listen.listen' => [
                'service' => 'Seeker\\Service\\Common\\EventListen:listen',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_COMMON
            ],
            'common.event_listen.remove' => [
                'service' => 'Seeker\\Service\\Common\\EventListen:remove',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_COMMON
            ],
            //节点部分...Node调用。
            'manager.deploy.progress' => [
                'service' => 'Seeker\\Manager\\Service\\Deploy:progress',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_NODE
            ],

            //节点部分...客房端调用
            'manager.node.add' => [
                'service' => 'Seeker\\Manager\\Service\\Node:add',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],

            'manager.node.remove' => [
                'service' => 'Seeker\\Manager\\Service\\Node:remove',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],

            //客房端调用
            'manager.node.lists' => [
                'service' => 'Seeker\\Manager\\Service\\Node:lists',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL | Connection::AUTHED_NODE
            ],
            //部署部分 , 客房端调用
            'manager.node.deploy' => [
                'service' => 'Seeker\\Manager\\Service\\Node:deploy',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],
            //进程管理部分 , 客房端调用
            'manager.node.start_process' => [
                'service' => 'Seeker\\Manager\\Service\\Node:startProcess',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],

            'manager.node.stop_process' => [
                'service' => 'Seeker\\Manager\\Service\\Node:stopProcess',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],

            'manager.node.remove_process' => [
                'service' => 'Seeker\\Manager\\Service\\Node:removeProcess',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],
            //Service部分.. , 客房端调用
            'manager.node.start_service' => [
                'service' => 'Seeker\\Manager\\Service\\Node:startService',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],

            'manager.node.stop_service' => [
                'service' => 'Seeker\\Manager\\Service\\Node:stopService',
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base',
                'authed' => Connection::AUTHED_TOOL
            ],
        ]);

        $this->dispatcher->remoteCalls([
            'node.deploy.push' => [
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
            'common.node.login' => [
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
            'node.deploy.remove' => [
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
            'common.event_listen.listen' => [
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
            'common.event_listen.remove' => [
                'request' => 'Seeker\\Protocol\\Json',
                'response' => 'Seeker\\Protocol\\Base'
            ],
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