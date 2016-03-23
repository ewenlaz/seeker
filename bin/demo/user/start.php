<?php
//会传入变量$di 需要向Di注入 service_process....
use Seeker\Service\Process;

$di->get('loader')->registerNamespaces([
 'User' => __DIR__ . '/src/'
], true);

Console::debug('user demo..注册完成。。。。');

$di->set('service_process', function() {
    return new Process;
});