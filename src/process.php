<?php

class Dependencies
{
    protected $vendor = null;
    protected $dependencies = [];
    protected $loader = null;
    public function __construct($vendor)
    {
        $this->vendor = $vendor;
        $this->loader = new Loader();
    }

    protected function getConfigPath($name = '', $version = '')
    {
        $path = $this->getRootPath($name, $version);
        $file = $path . 'config.json';
        if (!is_file($file)) {
            throw new Exception('config not exists:' . $file, 1);
        }
        return $file;
    }

    protected function getRootPath($name = '', $version = '')
    {

        $path = $this->vendor . '/' . $name . '/' . $version . '/';
        $path = str_replace('//', '/', $path);
        if (!is_dir($path)) {
            throw new Exception('path not exists:' . $path, 1);
        }
        return $path;
    }

    public function loadRoot($name = '', $version = '')
    {
        //加载配置文件。。
        $this->add($name, $version);
        $config = file_get_contents($this->getConfigPath($name, $version));
        $config = json_decode($config, true);
        if (isset($config['dependencies']) && is_array($config['dependencies'])) {
            foreach ($config['dependencies'] as $dependence => $version) {
                $this->loadRoot($dependence, $version);
            }
        }
    }

    public function start()
    {
        $loader = $this->loader;
        if (!isset($this->dependencies['seeker'])) {
            //加载默认的运行框架。
            require __DIR__ . '/start.php';
        } else {
            //获取Version...
            $version = $this->dependencies['seeker'];
            $seekerPath =  $this->getRootPath('seeker', $version) . 'start.php';
            require $seekerPath;
        }
        unset($this->dependencies['seeker']);
        foreach ($this->dependencies as $name => $version) {
            $path = $this->getRootPath($name, $version) . '/start.php';
            require $path;
        }
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

//获取URL参数 。
$shortOpts = '';
$longOpts = [
    'node:',
    'process:',
    'version:',
    'vendor:'
];

$params = getopt($shortOpts, $longOpts);
$dependencies = new Dependencies($params['vendor']);
$dependencies->loadRoot($params['process'], $params['version']);
$dependencies->start();


// use Phalcon\Loader;
// use Phalcon\DI;
// use Phalcon\Di\FactoryDefault\Cli;

// class CliDi extends Cli
// {

//     public function __construct()
//     {

//     }

// }

// // $di2 = new DI;


// class Test implements Phalcon\Di\InjectionAwareInterface
// {
//     protected $di;
//     public function setDI(Phalcon\DiInterface $di) {
//         $this->di = $di;
//         var_dump($this->di);
//     }

//     public function getDI()
//     {
//         return $this->di;
//     }

//     public function dd()
//     {
//         $this->di['abc']->a = 'value';
//     }
// }

// $di = new CliDi;
// $di->set('abc', function() {
//     return new StdClass;
// });

// $di->set('test', function() {
//     return new Test;
// });

// // print_r($di);
// $test = new test();
// $di['test']->dd();
// var_dump($di['abc']);

// //print_r(DI::getDefault());

// exit;
// class Dependencies
// {
//     protected $vendor = null;
//     protected $dependencies = [];
//     protected $loader = null;
//     public function __construct($vendor)
//     {
//         $this->vendor = $vendor;
//         $this->loader = new Loader();
//     }

//     protected function getConfigPath($name = '', $version = '')
//     {
//         $path = $this->getRootPath($name, $version);
//         $file = $path . 'config.json';
//         if (!is_file($file)) {
//             throw new Exception('config not exists:' . $file, 1);
//         }
//         return $file;
//     }

//     protected function getRootPath($name = '', $version = '')
//     {

//         $path = $this->vendor . '/' . $name . '/' . $version . '/';
//         $path = str_replace('//', '/', $path);
//         if (!is_dir($path)) {
//             throw new Exception('path not exists:' . $path, 1);
//         }
//         return $path;
//     }

//     public function loadRoot($name = '', $version = '')
//     {
//         //加载配置文件。。
//         $this->add($name, $version);
//         $config = file_get_contents($this->getConfigPath($name, $version));
//         $config = json_decode($config, true);
//         if (isset($config['dependencies']) && is_array($config['dependencies'])) {
//             foreach ($config['dependencies'] as $dependence => $version) {
//                 $this->loadRoot($dependence, $version);
//             }
//         }
//     }

//     public function start()
//     {
//         $loader = $this->loader;
//         if (!isset($this->dependencies['seeker'])) {
//             //加载默认的运行框架。
//             require __DIR__ . '/start.php';
//         } else {
//             //获取Version...
//             $version = $this->dependencies['seeker'];
//             $seekerPath =  $this->getRootPath('seeker', $version) . 'start.php';
//             require $seekerPath;
//         }
//         unset($this->dependencies['seeker']);
//         foreach ($this->dependencies as $name => $version) {
//             $path = $this->getRootPath($name, $version) . '/start.php';
//             require $path;
//         }
//     }

//     protected function add($name = '', $version = '')
//     {
//         //Todo Version 判断。大于，小于，不等于。。。。。
//         if (isset($dependencies[$name]) && $dependencies[$name] != $version) {
//             $msg = sprintf('dependencies defined. and version not vaild(%s:%s)!', $dependencies[$name], $version);
//             throw new Exception($msg, 1);
//         }
//         return $this->load($name, $version);
//     }

//     protected function load($name = '', $version = '')
//     {
//         $this->dependencies[$name] = $version;
//     }
// }

// //获取URL参数 。
// $shortOpts = '';
// $longOpts = [
//     'node:',
//     'process:',
//     'version:',
//     'vendor:'
// ];


// $params = getopt($shortOpts, $longOpts);
// print_r($params);
// $dependencies = new Dependencies($params['vendor']);
// $dependencies->loadRoot($params['process'], $params['version']);
// $dependencies->start();

