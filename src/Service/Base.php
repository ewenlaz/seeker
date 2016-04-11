<?php

namespace Seeker\Service;

use Seeker\Message\ServiceBus;
use Seeker\Proto\Wrapper;
use Seeker\Standard\Connection;
use Seeker\Service\Remote;

class Base
{
    protected $messageBus = null;
    public function __construct(ServiceBus $bus, Connection $connection, Wrapper $proto)
    {
        $this->messageBus = $bus;
        $this->connection = $connection;
        $this->protoManager = $this->messageBus->protoManager;
        $this->proto = $proto;
        $this->request = &$proto->request; // new 
        $this->response = &$proto->response;// new 
    }

    public function send()
    {
        $this->messageBus->sendServiceResponse($this->connection, $this->proto);
    }

    public function call($service)
    {
        return new Remote($service, $this->messageBus, $this->connection);
    }
}