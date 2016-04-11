<?php

namespace Seeker\Service;

use Seeker\Standard\Connection;
use Seeker\Proto\Wrapper;
use Seeker\Message\ServiceBus;

class Remote
{
    public $messageBus = null;
    public $serviceName = '';
    public $connection = null;
    protected $wrapper = false;
    protected $callback = null;
    public function __construct($serviceName = '', ServiceBus $messageBus, Connection $connection)
    {
        $this->serviceName = $serviceName;
        $this->messageBus = $messageBus;
        $this->connection = $connection;
        $this->wrapper = new Wrapper('', $serviceName);
        $handler = $this->messageBus->protoManager->getHandler($this->wrapper->service);
        //考虑不存在的情况
        if (!$handler) {
            throw new \Exception("the handler not found....:" . $this->serviceName, 1);
        }
        $this->wrapper->attachRequest(new $handler->request);
    }

    public function then($callback)
    {
        $this->callback = $callback;
        $this->setRequired();
        return $this;
    }

    public function send()
    {
        $this->messageBus->sendRemoteCall($this->connection, $this->wrapper, $this->callback);
    }

    public function setRequired()
    {
        $this->wrapper->flag | Wrapper::FLAG_REQUIRED;
        return $this;
    }

    public function setBroadcast()
    {
        $this->wrapper->flag | Wrapper::FLAG_BROADCAST;
        return $this;
    }

    public function setNode($node = 0)
    {
        $this->wrapper->node = $node;
        return $this;
    }

    public function setProcess($process = 0)
    {
        $this->wrapper->process = $process;
        return $this;
    }

    function __call($method, $args)
    {
        call_user_func_array([$this->wrapper->request, $method], $args);
        return $this;
    }
}