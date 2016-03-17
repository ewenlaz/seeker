<?php

use Seeker\Server\Connection;
return [
    'remoteCalls' => [
        'user.common.login' => [
            'request' => 'Seeker\\Protocol\\Json',
            'response' => 'Seeker\\Protocol\\Base'
        ]
    ]
];