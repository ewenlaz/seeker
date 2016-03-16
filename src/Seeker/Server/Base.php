<?php

namespace Seeker\Server;

use Seeker\Server\Standard\ListenerInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;

class Base implements InjectionAwareInterface
{

    const TYPE_SOCK_TCP = SWOOLE_SOCK_TCP;

    protected $listeners = null;
    protected $swServer = null;
    protected $processes = null;
    protected $di;

    public function __construct()
    {
        $this->listeners = new \SplObjectStorage;
        $this->processes = new \SplObjectStorage;
    }

    public function setDI(DiInterface $di) {
        $this->di = $di;
    }

    public function getDI()
    {
        return $this->di;
    }

    public function addTask(Task $task)
    {

    }

    public function addProcess($process)
    {
        $this->processes->attach($process);
    }

    public function addListener(ListenerInterface $listener)
    {
        $this->listeners->attach($listener);
    }

    public function start()
    {
        foreach ($this->listeners as $listener) {
            if (!$this->swServer) {
                $this->swServer = new \Swoole\Server($listener->getHost(), $listener->getPort(), SWOOLE_PROCESS, $listener->getType());
                $this->swServer->set($listener->getSetting());
            } else {
                $port = $this->swServer->listen($listener->getHost(), $listener->getPort(), $listener->getType());
                $port->set($listener->getSetting());
            }
            $this->addSwooleEvent($listener);
        }

        foreach ($this->processes as $process) {
            $this->swServer->addProcess($process->getSwooleProcess());
        }

        $this->swServer->start();
    }

    public function addSwooleEvent($listener)
    {
        $server = $this;
        $this->swServer->on('WorkerStart', function($swServer, $id) use ($listener, $server) {
            //调用相应的Worker ...
            $listener->getWorker()->setPid(posix_getpid());
            $listener->getWorker()->setId($id);
            $listener->getWorker()->setServer($server);
            $listener->getWorker()->onStart();
        });

        $this->swServer->on('Connect', function($swServer, $fd) use ($listener, $server) {
            $listener->getWorker()->onConnect($fd);
        });

        $this->swServer->on('Close', function($swServer, $fd) use ($listener, $server) {
            $listener->getWorker()->onClose($fd);
        });

        $this->swServer->on('receive', function($swServer, $fd, $fromId, $data) use ($listener, $server) {
            $listener->getWorker()->onReceive($fd, $data);
        });
    }

    public function send($fd, $data = '')
    {
        return $this->swServer->send($fd, $data);
    }

    public function close($fd)
    {
        return $this->swServer->close($fd);
    }
}