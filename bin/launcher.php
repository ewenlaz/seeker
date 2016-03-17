<?php

use Seeker\Server\Base;
use Seeker\Server\Tcp\Listener;
use Seeker\Service\ServiceProcessManager;
use Seeker\Service\Dispatcher;
use Seeker\Protocol\Base\Setting;
use Seeker\Service\Worker;
use Seeker\Standard\ConnectionInterface;
use Seeker\Protocol\AskId;

$di = require __DIR__ . '/../start.php';

//从配置上获取监听端口
$params = getopt('', ['port:', 'host:', 'type:', 'exec-php:', 'tmp:']);

$type = isset($params['type']) && $params['type'] ? $params['type'] : 'node';

if (!in_array($type, ['node', 'master'])) {
    Console::debug('节点类型不支持%s', $type);
}
//生成配置信息。。。。
//检查配置表....

$tmpPath = isset($params['tmp']) ? $params['tmp'] : __DIR__ . '/tmp/';
if (!is_dir($tmpPath)) {
    Console::debug('创建临时目录');
    mkdir($tmpPath);
}
$keyFile = $tmpPath . 'key.txt';
$keys = [];
if (file_exists($keyFile)) {
    $keys = json_decode(file_get_contents($keyFile), true);
    if (is_array($keys)) {
        Console::debug('从缓存文件恢复ＫＥＹ信息');
    } else {
        Console::debug('从缓存文件恢复ＫＥＹ失败。内容有错。请删后重新启动。');
        exit;
    }
} else {
    Console::debug('开始生成KEY....');
    $random = new \Phalcon\Security\Random();
    $master = $random->hex(16);//Master. 连接ＫＥＹ。
    Console::debug('master key:' . $master);
    $harbor = $random->hex(16);
    Console::debug('harbor key:' . $master);
    $tool   = $random->hex(16);
    Console::debug('tool key:' . $master);
    $common   = $random->hex(16);
    Console::debug('common key:' . $master);
    $keys = [
        $master => ConnectionInterface::AUTHED_MASTER,
        $harbor => ConnectionInterface::AUTHED_HARBOR,
        $tool => ConnectionInterface::AUTHED_TOOL,
        $common => ConnectionInterface::AUTHED_COMMON,
    ];

    file_put_contents($keyFile, json_encode($keys));
}

$di->set('auth_keys', function() use ($keys) {
    return $keys;
});

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

if (!isset($execs['php'])) {
    $execs['php'] = $_SERVER['_'];
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
Console::debug('启动端ＡＳＫＩＤ原子计数器');
$askIdCreater = new AskId;
$di->set('askId', function() use ($askIdCreater) {
    return $askIdCreater;
});

Console::debug('开始启动端口监听');
$server->start();
