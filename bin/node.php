<?php
use Seeker\Server\Node;

define('SOCKET_FILE', __DIR__ . '/node.sock');


spl_autoload_register(function($class) {
    require_once __DIR__ . '/../src/' . str_replace(['Seeker\\', '\\'], ['', '/'], $class) .'.php';
});

$process = new Node;