<?php
namespace Seeker\Service\Dispatcher\Adapter;

use Seeker\Service\Dispatcher\AdapterInterface;
use Seeker\Standard\ConnectionInterface;

class Remote
{
    protected $serviceName = '';
    protected $request = '';
    protected $response = '';

    public function __construct($name, $config)
    {
        $this->serviceName = $name;
        if (isset($config['request'])) {
            $this->request = $config['request'];
        } else {
            throw new \Exception('Dispatcher Adapter local request not vaild: request is null', 1);
        }

        if (isset($config['response'])) {
            $this->response = $config['response'];
        } else {
            throw new \Exception('Dispatcher Adapter local response not vaild: response is null', 1);
        }
    }

    public function createRequest()
    {
        $request = $this->request;
        if (!class_exists($request)) {
            throw new \Exception('request class undefined:'. $request, 1);
        }
        $request = new $request;
        return $request;
    }

    public function createResponse()
    {
        $response = $this->response;
        if (!class_exists($response)) {
            throw new \Exception('response class undefined:'. $response, 1);
        }
        $response = new $response;
        return $response;
    }
}