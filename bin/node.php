<?php


include 'autoload.php';


$settings = [
    'auth_keys' => [
        'manager' => 'node_10000'
    ]
];

shared('setting', function() use ($settings) {
    return new Setting($settings);
});

$boots = new Seeker\Node\Boots;
$boots->start();