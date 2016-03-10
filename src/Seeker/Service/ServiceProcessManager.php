<?php
namespace Seeker\Service;

use Seeker\Server\Standard\WorkerInterface;
use Seeker\Server\Standard\WorkerBaseTrait;
use Swoole\Process;

class ServiceProcessManager implements WorkerInterface
{
    use WorkerBaseTrait;

    protected $swProcess = null;
    protected $dataPipe = '';
    protected $execFiles = [];
    protected $autoloadServiceProcess = [];

    public function __construct($execFiles = [])
    {
        $this->execFiles = $execFiles;
        if (!$this->execFiles) {
            throw new \Exception("you must config execFiles : [php => /use/local/php]", 1);
        }
        $this->swProcess = new Process([$this, 'onSwooleProcessStart'], false, 2);
    }

    public function autoloadServiceProcess($autoloadServiceProcess)
    {
        $this->autoloadServiceProcess = $autoloadServiceProcess;
        return $this;
    }

    // public function createServiceProcess($data)
    // {
    //     $data = [
    //         'cmd' => 'create_service_process',
    //         'data' => $data
    //     ];
    //     return $this->swProcess->write(json_encode($data) . "\n");
    // }

    // public function dispatch($data = '')
    // {
    //     if (!$data) {
    //         return;
    //     }
    //     $this->dataPipe .= $data;
    //     while (strpos($this->dataPipe, "\n") !== false) {
    //         $cmd = explode("\n", $this->dataPipe, 2);
    //         $this->dataPipe = $cmd[1];
    //         $cmd = json_decode($cmd[0]);
    //         if ($cmd && $cmd['cmd']) {
    //             switch ($cmd['cmd']) {
    //                 case 'create_service_process':
    //                     $this->createProcesses($cmd['data']);
    //                 break;
    //             }
    //         }
    //     }
    // }

    public function createProcesses($option)
    {
        $exec = $option['exec'];
        if (!isset($this->execFiles[$exec])) {
            echo 'start service not found exec:' . $exec . PHP_EOL;
            return;
        }
        $option['exec'] = $this->execFiles[$exec];
        $swProcess = new Process(function($swProcess) use ($option)  {
            $startOption = $option;
            unset($startOption['exec']);
            unset($startOption['path']);
            $optionString = '';
            foreach ($startOption as $name => $val) {
                $optionString .= '--'.$name .'='. $val . ' ';
            }
            echo 'start service process:' . $option['exec'] . ',' . $option['path'] . ',' . $optionString . PHP_EOL;
            $swProcess->exec($option['exec'], [$option['path'], trim($optionString)]);
        });
        $swProcess->start();
    }

    public function getSwooleProcess()
    {
        return $this->swProcess;
    }

    public function onStart()
    {
        echo 'ServiceProcess Start....' . PHP_EOL;
        var_dump($this->autoloadServiceProcess);
        foreach ($this->autoloadServiceProcess as $option) {
            $this->createProcesses($option);
        }
        // //读取信号。
        // while (true) {
        //     # code...

        //     echo 'xxxxx' . PHP_EOL;

        //     $ret = Process::wait(false);
        //     if ($ret) {
        //         //重新拉起。。
        //     }
        //     //获取通道数据。。。
        //     $this->dispatch($this->swProcess->read());
        //     sleep(1);
        // }
    }

    public function onReadPipe()
    {
        $recv = $this->swProcess->read();
        $cmd = json_decode($recv);
        if ($cmd && $cmd['cmd']) {
            switch ($cmd['cmd']) {
                case 'create_service_process';
                    $this->createProcesses($cmd['data']);
                break;
            }
        }

    }

    public function onChildExit()
    {
        while ($ret =  Process::wait(false)) {
            echo 'process exit....' . PHP_EOL;
        }
    }

    public function onSwooleProcessStart()
    {
        swoole_event_add($this->swProcess->pipe, [$this, 'onReadPipe']);
        Process::signal(SIGCHLD, [$this, 'onChildExit']);
        $this->onStart();
    }

    public function onStop()
    {

    }
}