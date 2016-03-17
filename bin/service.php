<?php

use Seeker\Service\Dispatcher;
use Seeker\Protocol\AskId;

function get_real_path($startFile)
{
    $pwd = $_SERVER['PWD'] . '/';
    if (strpos($startFile, '/') !== 0) {

        $_startFile = $pwd . $startFile;
        if (!$startFile = realpath($_startFile)) {
            throw new Exception('dir or file not found:' . $_startFile, 1);
        }
    }
    return $startFile;
}

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

//获取URL参数 。
$longOpts = [
    'host:',
    'port:',
    'process:',
    'version:',
    'vendor:',
    'tmp:',
    'key:',
    'id:'
];

$params = getopt('', $longOpts);
print_r($params);

$startFile = get_real_path($_SERVER['PHP_SELF']);
echo 'startFile:' . $startFile . PHP_EOL;
$vendorPath = get_real_path(isset($params['vendor']) && $params['vendor'] ? $params['vendor'] : './vendor');
echo 'vendorPath:' . $vendorPath . PHP_EOL;

$tmpPath = get_real_path(isset($params['tmp']) && $params['tmp'] ? $params['tmp'] : './tmp');
echo 'tmpPath:' . $tmpPath . PHP_EOL;

$params['tmp'] = $tmpPath;
$params['vendor'] = $vendorPath;
$params['host'] = isset($params['host']) ? $params['host'] : '0.0.0.0';
$params['port'] = isset($params['port']) ? $params['port'] : '9901';
$params['key'] = isset($params['key']) ? $params['key'] : '';
if (!$params['key']) {
    echo '启动失败, 缺少连接ＫＥＹ。' . PHP_EOL;
    exit;
}
$params['id'] = isset($params['id']) ? $params['id'] : '';
if (!$params['id']) {
    echo '启动失败, 缺少连接id。' . PHP_EOL;
    exit;
}
$debugMaps = [];
//处理相关的Debug ....
foreach ($_SERVER['argv'] as $arg) {
    if (strpos($arg, '--debug-') !== 0) {
        continue;
    }
    list($debug, $dir) = explode('=', substr($arg, 8), 2);
    $dir = get_real_path($dir);
    $debugMaps[str_replace('-', '_', $debug)] = $dir;
}


class Dependencies
{
    protected $vendor = null;
    protected $dependencies = [];
    protected $debugMaps = [];
    public function __construct($vendor, $debugMaps = [])
    {
        $this->vendor = $vendor;
        $this->debugMaps = $debugMaps;
    }

    protected function getConfigPath($name = '', $version = '')
    {
        $path = $this->getRootPath($name, $version);
        $file = $path . 'config.php';
        if (!is_file($file)) {
            throw new Exception('config not exists:' . $file, 1);
        }
        return $file;
    }

    protected function getRootPath($name = '', $version = '')
    {
        if (isset($this->debugMaps[$name])) {
            return $this->debugMaps[$name] . '/';
        } else {
            $_path = $this->vendor . '/' . $name . '/' . $version . '/';
            return get_real_path($_path);
        }
        
    }

    public function loadRoot($name = '', $version = '')
    {
        $this->root = $name;
        $this->rootVersion = $version;
        $this->loadReal($name, $version);
    }

    public function loadReal($name = '', $version = '')
    {
        //加载配置文件。。
        $config = include ($this->getConfigPath($name, $version));
        if (isset($config['dependencies']) && is_array($config['dependencies'])) {
            foreach ($config['dependencies'] as $dependence => $version) {
                $this->loadReal($dependence, $version);
            }
        }
        $this->add($name, $version);
    }

    public function start()
    {
        if (!isset($this->dependencies['seeker'])) {
            //加载默认的运行框架。
            $di = require __DIR__ . '/../start.php';
        } else {
            //获取Version...
            $version = $this->dependencies['seeker'];
            $seekerPath =  $this->getRootPath('seeker', $version) . 'start.php';
            $di = require $seekerPath;
        }
        $this->registerDefault($di);

        unset($this->dependencies['seeker']);
        //load base protocol

        loadDispatcherConfig($di->get('dispatcher'), 'config/protocol_service.php');

        foreach ($this->dependencies as $name => $version) {
            $protocol = $this->getRootPath($name, $version) . '/protocol.php';
            if (realpath($protocol)) {
                loadDispatcherConfig($di->get('dispatcher'), realpath($protocol));
            }
            $path = $this->getRootPath($name, $version) . '/start.php';
            require $path;
        }

        

        return $di;
    }

    protected function registerDefault($di)
    {
        $askIdCreater = new AskId;
        $di->set('askId', function() use ($askIdCreater) {
            return $askIdCreater;
        });

        $dispatcher = new Dispatcher();
        $di->set('dispatcher', function() use ($dispatcher) {
            return $dispatcher;
        });
    }

    protected function add($name = '', $version = '')
    {
        //Todo Version 判断。大于，小于，不等于。。。。。
        if (isset($dependencies[$name]) && $dependencies[$name] != $version) {
            $msg = sprintf('dependencies defined. and version not vaild(%s:%s)!', $dependencies[$name], $version);
            throw new Exception($msg, 1);
        }
        return $this->load($name, $version);
    }

    protected function load($name = '', $version = '')
    {
        $this->dependencies[$name] = $version;
    }
}

$dependencies = new Dependencies($vendorPath, $debugMaps);
$dependencies->loadRoot($params['process'], $params['version']);
$di = $dependencies->start();

Console::debug('依赖加载完成');
$di->set('params', function() use ($params) {
    return $params;
});
$di['service_process']->onStart();