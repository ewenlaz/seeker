<?php
namespace Seeker\Service\Dispatcher\Adapter;

use Seeker\Service\Dispatcher\AdapterInterface;
use Seeker\Standard\ConnectionInterface;
use Seeker\Protocol\Base;
use Seeker\Service\Dispatcher;

class Local implements AdapterInterface
{
    protected $serviceName = '';
    protected $authed = 0;
    public $service = '';
    public $request = '';
    public $response = '';

    public function __construct($name, $config)
    {
        $this->serviceName = $name;
        if (isset($config['service']) && strpos($config['service'], ':')) {
            $this->service = $config['service'];
        } else {
            throw new \Exception('Dispatcher Adapter local service config not vaild:' . $config['service'], 1);
        }

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

        if (isset($config['authed'])) {
            $this->authed = (int)$config['authed'];
        }
    }

    public function getService()
    {
        return $this->serviceName;
    }

    public function requeireAuthed()
    {
        return $this->authed;
    }

    public function dispatch(Dispatcher $dispatch, ConnectionInterface $connection, $header, $body)
    {

        list($service, $method) = explode(':', $this->service);
        $request = $this->request;
        if (!class_exists($request)) {
            throw new \Exception('request class undefined:'. $request, 1);
        }
        $request = new $request;
        $request->setHeaders($header);
        $request->setBodyStream($body);
        $response = $this->response;
        if (!class_exists($response)) {
            throw new \Exception('response class undefined:'. $response, 1);
        }
        $response = new $response;
        $respHeader = Base::headerToResponse($header);
        $response->setHeaders($respHeader);

        if (!class_exists($service)) {
            throw new \Exception('service class undefined:'. $service, 1);
        }
        
        $service = new $service($dispatch, $connection, $request, $response);

        if (!method_exists($service, $method)) {
            throw new \Exception('method undefined:'. $this->service, 1);
        }
        return $ret = $service->$method();
    }
}