<?php
namespace Seeker\Service;

use Seeker\Server\Tcp\Worker as TcpWorker;
use Seeker\Service\Dispatcher;
use Seeker\Server\Connection;

class Worker extends TcpWorker
{

    protected $dispatcher = null;
    protected $nodes = [];
    protected $clients = [];

    public function onStart()
    {
        //启动。。注册协议监听。。
        \Console::debug('worker start....');
        $this->dispatcher = $this->getServer()->getDI()->get('dispatcher');
    }

    public function onReceive($id, $data)
    {
        $this->dispatcher->dispatch($this->clients[$id], $data);
    }

    public function onConnect($id)
    {
        $this->clients[$id] = new Connection($id, $this);
    }
}