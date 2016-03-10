<?php


include 'autoload.php';

use Seeker\Standard\ConnectionInterface;


$settings = [
    'authKeys' => [
        'tool' => ConnectionInterface::AUTHED_COMMON | ConnectionInterface::AUTHED_TOOL
    ],
    'exec' => [
        'php' => '/usr/local/php/bin/php'
    ],
    'autoloadServiceProcess' => [
        [
            'exec' => 'php',
            'process' => 'user',
            'version' => '2.2.0',
            'path' => __DIR__ . '/demo/service_process/user/start.php'
        ]
    ]
];

shared('setting', function() use ($settings) {
    return new Setting($settings);
});

$boots = new Seeker\Manager\Boots;
$boots->start();