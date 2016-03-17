<?php
use Seeker\Server\Connection;

return [
    'listens' => [
        // 'master.deploy.progress' => [
        //     'service' => 'Seeker\\Service\\Master\\Deploy:progress',
        //     'request' => 'Seeker\\Protocol\\Json',
        //     'response' => 'Seeker\\Protocol\\Base',
        //     'authed' => Connection::AUTHED_NODE
        // ]
    ],
    'remoteCalls' => [
        'common.node.login' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base'
        ],
        'node.client.listens' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base'
        ]
    ]
];