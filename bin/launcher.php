<?php

use Seeker\Server\Base;
use Seeker\Server\Tcp\Listener;
use Seeker\Service\ServiceProcessManager;
use Seeker\Service\Dispatcher;
use Seeker\Protocol\Base\Setting;
use Seeker\Service\Worker;

echo com_create_guid();

$di = require __DIR__ . '/../start.php';

//从配置上获取监听端口
$params = getopt('', ['port:', 'host:', 'type:', 'exec-php:', 'tmp:']);

$type = isset($params['type']) && $params['type'] ? $params['type'] : 'node';

if (!in_array($type, ['node', 'master'])) {
    Console::debug('节点类型不支持%s', $type);
}

$di->set('server', function() {
    return new Base;
});

$dispatcher = new Dispatcher();

function loadDispatcherConfig($dispatcher, $file)
{
    //从Node配置文件读取信息。。
    $config = new Phalcon\Config\Adapter\Php($file);
    //注册给dispatcher.
    isset($config->listens) && $dispatcher->listens($config->listens);
    isset($config->remoteCalls) && $dispatcher->remoteCalls($config->remoteCalls);
    isset($config->listenEvents) && $dispatcher->listenEvents($config->listenEvents);
    isset($config->productEvents) && $dispatcher->productEvents($config->productEvents);
}

//加载Node配置

loadDispatcherConfig($dispatcher, 'config/protocol_node.php');
if ($type === 'master') {
    loadDispatcherConfig($dispatcher, 'config/protocol_master.php');
}

$di->set('dispatcher', function() use ($dispatcher) {
    return $dispatcher;
});

Console::debug('配置完成');

$server = $di->get('server');


//获取执行文件。。
$execs = [];
foreach ($params as $key => $val) {
    if (strpos($key, 'exec-') === 0) {
        $execs[substr($key, 5)] = $val;
    }
}
$serviceProcessManager = new ServiceProcessManager($execs);
$server->addProcess($serviceProcessManager);

//Worker
$worker = new Worker;

$host = $params['host'] ? $params['host'] : '127.0.0.1';
$port = $params['port'] ? (int)$params['port'] : 9901;

$listener = new Listener($port, $host);
$listener->setWorker($worker);
$setting = Setting::eof();
$setting['worker_num'] = 1;
$listener->setting($setting);
$server->addListener($listener);

Console::debug('开始启动端口监听');
$server->start();



// $settings = [
//     'authKeys' => [
//         'tool' => ConnectionInterface::AUTHED_COMMON | ConnectionInterface::AUTHED_TOOL
//     ],
//     'exec' => [
//         'php' => '/usr/local/php/bin/php'
//     ],
//     'autoloadServiceProcess' => [
//         [
//             'exec' => 'php',
//             'process' => 'user',
//             'version' => '2.2.0',
//             'path' => __DIR__ . '/demo/service_process/user/start.php'
//         ]
//     ]
// ];

