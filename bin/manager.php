<?php


include 'autoload.php';

use Seeker\Standard\ConnectionInterface;


$settings = [
    'authKeys' => [
        'tool' => ConnectionInterface::AUTHED_COMMON | ConnectionInterface::AUTHED_TOOL
    ]
];

shared('setting', function() use ($settings) {
    return new Setting($settings);
});

$boots = new Seeker\Manager\Boots;
$boots->start();