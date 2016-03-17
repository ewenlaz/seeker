<?php

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

//获取URL参数 。
$longOpts = [
    'node:',
    'process:',
    'version:',
    'vendor:',
    'tmp:'
];

$params = getopt('', $longOpts);
print_r($params);

$startFile = get_real_path($_SERVER['PHP_SELF']);
echo 'startFile:' . $startFile . PHP_EOL;
$vendorPath = get_real_path(isset($params['vendor']) && $params['vendor'] ? $params['vendor'] : './vendor');
echo 'vendorPath:' . $vendorPath . PHP_EOL;

$tmpPath = get_real_path(isset($params['tmp']) && $params['tmp'] ? $params['tmp'] : './tmp');
echo 'tmpPath:' . $tmpPath . PHP_EOL;

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
        //加载配置文件。。
        $this->add($name, $version);
        $config = include ($this->getConfigPath($name, $version));
        if (isset($config['dependencies']) && is_array($config['dependencies'])) {
            foreach ($config['dependencies'] as $dependence => $version) {
                $this->loadRoot($dependence, $version);
            }
        }
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
        unset($this->dependencies['seeker']);
        foreach ($this->dependencies as $name => $version) {
            $path = $this->getRootPath($name, $version) . '/start.php';
            require $path;
        }
        return $di;
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

print_r($debugMaps);

$dependencies = new Dependencies($vendorPath, $debugMaps);
$dependencies->loadRoot($params['process'], $params['version']);
$di = $dependencies->start();

Console::debug('依赖加载完成');

$di['service_process']->start();