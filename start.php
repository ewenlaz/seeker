<?php

use Phalcon\Loader;
use Seeker\Core\DIFactory;

$loader = new Loader;
$loader->registerNamespaces([
    'Seeker' => __DIR__ . '/src/Seeker/'
]);

$loader->register();

$di = new DIFactory;
$di->set('loader', function() use ($loader) {
    return $loader;
}) ;


class Console
{
    public static function debug($data)
    {
        echo sprintf('[%s][%8s] > %s', date('Y-m-d H:i:s'), 'DEBUG', $data) . PHP_EOL;
    }
}

return $di;