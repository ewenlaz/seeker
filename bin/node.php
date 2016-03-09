<?php

use Seeker\Standard\ConnectionInterface;

include 'autoload.php';


$settings = [
    'authKeys' => [
        'node_10000' => ConnectionInterface::AUTHED_MANAGER
    ]
];

shared('setting', function() use ($settings) {
    return new Setting($settings);
});

$boots = new Seeker\Node\Boots;
$boots->start();