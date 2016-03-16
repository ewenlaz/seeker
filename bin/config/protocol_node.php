<?php
use Seeker\Server\Connection;

return [
	'listens' => [
        'common.node.login' => [
            'service' => 'Seeker\\Service\\Common\\Node:login',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base'
        ],
        'node.deploy.push' => [
            'service' => 'Seeker\\Service\\Node\\Deploy:push',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_MANAGER
        ],
        //进程管理部分 , 客房端调用
        'node.deploy.start_process' => [
            'service' => 'Seeker\\Service\\Node\\Deploy:startProcess',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_MANAGER
        ],

        'node.deploy.stop_process' => [
            'service' => 'Seeker\\Service\\Node\\Deploy:stopProcess',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_MANAGER
        ],

        'node.deploy.remove_process' => [
            'service' => 'Seeker\\Service\\Node\\Deploy:removeProcess',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_MANAGER
        ],
        //Service部分.. , 客房端调用
        'node.deploy.start_service' => [
            'service' => 'Seeker\\Service\\Node\\Deploy:startService',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_MANAGER
        ],
        'node.deploy.stop_service' => [
            'service' => 'Seeker\\Service\\Node\\Deploy:stopService',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base',
            'authed' => Connection::AUTHED_MANAGER
        ],
    ],
    'remoteCalls' => [
        'node.deploy.progress' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base'
        ]
    ]
];