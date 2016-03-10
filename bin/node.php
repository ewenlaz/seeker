<?php

use Seeker\Standard\ConnectionInterface;

include 'autoload.php';


$settings = [
    'authKeys' => [
        'node_10000' => ConnectionInterface::AUTHED_MANAGER
    ],
    'autoloadProcess' => [
        [
            'exec' => 'php',
            'process' => 'user',
            'version' => '2.2.0',
            'path' => __DIR__ . '/user/',
            'config' => ['a' => 1]
        ]
    ]
];

shared('setting', function() use ($settings) {
    return new Setting($settings);
});

$boots = new Seeker\Node\Boots;
$boots->start();