<?php
//会传入变量$di 需要向Di注入 service_process....
use Test\Process;

$di->get('loader')->registerNamespaces([
 'Test' => __DIR__ . '/src/'
], true);

Console::debug('test demo..注册完成。。。。');

$di->set('service_process', function() {
	return new Process;
});