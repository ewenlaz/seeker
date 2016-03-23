<?php

use Seeker\Server\Connection;
return [
    'listens' => [
        //节点部分...Node调用。
        'user.common.login' => [
            'service' => 'User\\Service\\Common:login',
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Json',
            'authed' => Connection::AUTHED_SERVICE
        ],
    ]
];