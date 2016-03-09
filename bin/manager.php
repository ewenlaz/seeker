<?php


include 'autoload.php';


$settings = [
    'auth_keys' => [
        'tool' => 'ab2cd'
    ]
];

shared('setting', function() use ($settings) {
    return new Setting($settings);
});

$boots = new Seeker\Manager\Boots;
$boots->start();