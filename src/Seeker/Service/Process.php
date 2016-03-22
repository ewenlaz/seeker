<?php
namespace Seeker\Service;

use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Seeker\Server\Standard\WorkerInterface;
use Seeker\Server\Standard\WorkerBaseTrait;

use Seeker\Service\ConnectClient;
use Seeker\Protocol\Error;
use Seeker\Protocol\Base\Setting;


class Process implements WorkerInterface, InjectionAwareInterface
{
    use WorkerBaseTrait;

    protected $di = null;
    protected $dispatcher = null;
    protected $connection = null;

    public function onStart()
    {
        $this->dispatcher = $this->getDI()->get('dispatcher');
        $params = $this->getDI()->get('params');

        //开始连接远程服务器。。。。
        $this->connection = new ConnectClient($params['host'], $params['port'], Setting::eof());
        $this->connection
            ->setNodeId($params['id'])
            ->setAuthKey($params['key'])
            ->connect();
        echo '......' . PHP_EOL;
    }

    public function onStop()
    {

    }

    public function getDI()
    {
        return $this->di;
    }

    public function setDI(DiInterface $di)
    {
        $this->di = $di;
    }
}