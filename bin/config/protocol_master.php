<?php
use Seeker\Server\Connection;

return [
    'listens' => [
        //节点部分...Node调用。
        'master.deploy.progress' => [
            'service' => 'Seeker\\Service\\Master\\Deploy:progress',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_NODE
        ],

        //节点部分...客房端调用
        'master.node.add' => [
            'service' => 'Seeker\\Service\\Master\\Node:add',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],

        'master.node.remove' => [
            'service' => 'Seeker\\Service\\Master\\Node:remove',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],

        //客房端调用
        'master.node.lists' => [
            'service' => 'Seeker\\Service\\Master\\Node:lists',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL | Connection::AUTHED_NODE
        ],
        //部署部分 , 客房端调用
        'master.node.deploy' => [
            'service' => 'Seeker\\Service\\Master\\Node:deploy',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],
        //进程管理部分 , 客房端调用
        'master.node.start_process' => [
            'service' => 'Seeker\\Service\\Master\\Node:startProcess',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],

        'master.node.stop_process' => [
            'service' => 'Seeker\\Service\\Master\\Node:stopProcess',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],

        'master.node.remove_process' => [
            'service' => 'Seeker\\Service\\Master\\Node:removeProcess',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],
        //Service部分.. , 客房端调用
        'master.node.start_service' => [
            'service' => 'Seeker\\Service\\Master\\Node:startService',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],

        'master.node.stop_service' => [
            'service' => 'Seeker\\Service\\Master\\Node:stopService',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_TOOL
        ],
    ],
    'remoteCalls' => [
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
        //进程管理部分 , 客房端调用
        'node.deploy.start_process' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
        ],
        'node.deploy.stop_process' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
        ],
        'node.deploy.remove_process' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
        ],
        //Service部分.. , 客房端调用
        'node.deploy.start_service' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
        ],
        'node.deploy.stop_service' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
        ],
    ]
];