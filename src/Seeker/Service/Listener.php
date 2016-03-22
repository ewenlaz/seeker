<?php
namespace Seeker\Service;

use Seeker\Standard\ConnectionInterface;

class Listener
{
    const EVENT_LISTEN  = 1;
    const EVENT_PRODUCT = 2;
    const REMOTE   = 3;
    const ACCEPT       = 4;

    protected $name = '';
    protected $type = 0;
    protected $authed = 0;
    protected $request = '';
    protected $response = '';
    protected $process = [];
    protected $service = '';
    protected $isProxy = false;
    protected $proxys = null;

    public function __construct($name = '', $type = self::ACCEPT)
    {
        $this->name = $name;
        $this->type = $type;
        $this->proxys = new \SplObjectStorage;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setAuthed($authed)
    {
        $this->authed = $authed;
    }

    public function getAuthed()
    {
        return $this->authed;
    }

    public function checkAuth($auth)
    {
        return !$this->authed || $auth & $this->authed;
    }

    public function addProxy(ConnectionInterface $connection)
    {

    }

    public function isProxy($val = null)
    {
        if (is_bool($val)) {
            $this->isProxy = $val;
        }
        return $this->isProxy;
    }

}