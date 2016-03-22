<?php
namespace Seeker\Service;

use Seeker\Protocol\Base\Setting;
use Seeker\Protocol\Base;
use Seeker\Protocol\Error;
use Seeker\Core\DI;
use Seeker\Standard\ConnectionInterface;
use Seeker\Service\Dispatcher\Adapter\Local;
use Seeker\Service\Dispatcher\Adapter\Remote;
use Seeker\Service\Dispatcher\AdapterInterface;
use Swoole\Timer;

class Dispatcher
{   

    const DISPATCH_LOCAL = 1;
    const DISPATCH_CONNECTION = 2;

    protected $serviceAccepts = [];
    protected $serviceRemotes = [];
    protected $nodeId = 0;
    protected $processId = 0;
    protected $callbacks = null;
    protected $isNode = false;

    public function __construct($nodeId = 0, $processId = 0)
    {
        $this->nodeId = $nodeId;
        $this->processId = $processId;
        $this->_callbacks = new \SplPriorityQueue;
    }

    public function startNode()
    {
        $this->isNode = true;
        Timer::tick(300, [$this, 'checkTimeout']);
    }

    public function checkTimeout()
    {
        foreach ($this->_callbacks as $callback) {

        }
    }

    public function getNodeId()
    {
        return $this->nodeId;
    }

    public function getProcessId()
    {
        return $this->processId;
    }

    public function listens($listens)
    {
        foreach ($listens as $service => $ctrl) {
            $adapter = new Local($service, $ctrl);
            $this->addAcceptAdapter($service, $adapter, true);
        }
    }

    public function getServiceAccepts()
    {
        return $this->serviceAccepts;
    }

    public function getServiceRemotes()
    {
        return $this->serviceRemotes;
    }

    public function remoteCalls($listens)
    {
        foreach ($listens as $service => $ctrl) {
            $local = new Remote($service, $ctrl);
            $this->addRemoteServiceCall($service, $local);
        }
    }

    public function addAcceptAdapter($service, AdapterInterface $adapter, $top = false)
    {
        $serviceId = crc32($service);
        if (!isset($this->serviceAccepts[$serviceId])) {
            $this->serviceAccepts[$serviceId] = new \StdClass;
            $this->serviceAccepts[$serviceId]->queue = new \SplQueue;
            $this->serviceAccepts[$serviceId]->name = $service;
        }
        if ($top) {
            $this->serviceAccepts[$serviceId]->queue->unshift($adapter);
        } else {
            $this->serviceAccepts[$serviceId]->queue->push($adapter);
        }
    }

    public function addRemoteServiceCall($service, $local = false)
    {
        $serviceId = crc32($service);
        if (!isset($this->serviceRemotes[$serviceId])) {
            $this->serviceRemotes[$serviceId] = new \StdClass;
            $this->serviceRemotes[$serviceId]->name = $service;
            $this->serviceRemotes[$serviceId]->local = $local;
        }
        if ($local) {
            $this->serviceRemotes[$serviceId]->local = $local;
        }
    }

    public function listenEvents($listens)
    {
        // foreach ($listens as $service => $ctrl) {
        //     $this->setServiceName($service);
        //     $this->listenEvents[crc32($service)] = $ctrl;
        // }
    }

    public function productEvents($listens)
    {
        // foreach ($listens as $service => $ctrl) {
        //     $this->setServiceName($service);
        //     $this->productEvents[crc32($service)] = $ctrl;
        // }
    }

    public function remoteCall($service)
    {
        //获取可用的来原。
        $serviceId = crc32($service);
        if (isset($this->serviceRemotes[$serviceId])) {
            $listen = $this->serviceRemotes[$serviceId];

            if ($listen->local) {
                $request = $listen->local->createRequest();
                $request->setAskId(DI::get('askId')->create());
                $request->setService($serviceId);
                $request->setFromNode($this->nodeId);
                $request->setFromProcess($this->processId);

                return $request;
            } else {
                \Console::debug(
                    'REMOTE_CALL: no found in local(service:%s)'
                    , $service
                );
                throw new \Exception(sprintf('REMOTE_CALL: no found (service:%s)', $service), 1);
            }
        } else {
            \Console::debug(
                'REMOTE_CALL: no found (service:%s)'
                , $service
            );
            throw new \Exception(sprintf('REMOTE_CALL: no found (service:%s)', $service), 1);
        }
    }

    public function registerOnBack($connection, $request)
    {
        $time = microtime(true);
        $connection->_callbacks[$request->getAskId()] = [
            'askTime' => $time,
            'callback' => $request->getCallback(),
            'isLocal' => true
        ];

        $connection->_lastCallback = $time;

        if ($this->isNode) {
            $this->_callbacks->insert($connection, 4000000000.0000 - $time);
        }
    }

// //找到被监听的RemoteCall....
//                     $response = $listener['response'];
//                     $response = new $response();
//                     $response->setHeaders($header);
//                     $response->setBodyStream(substr($data, Setting::eof()['package_body_offset']));
//                     $response->parseBody();

    public function onBack($connection, $header, $data) {
        //查找回调。。
        $serviceId = $header['service'];
        if (isset($connection->_callbacks[$header['askId']])) {
            $callback = $connection->_callbacks[$header['askId']];
            unset($connection->_callbacks[$header['askId']]);
            if ($callback['isLocal']) {
                $local = $this->serviceRemotes[$serviceId]->local;
                $response = $local->createResponse();
                $response->setHeaders($header);
                $response->setBodyStream(substr($data, Setting::eof()['package_body_offset']));
                $response->parseBody();
                call_user_func_array($callback['callback'], [$response, $connection]);
            } else {
                call_user_func_array($callback['callback'], [$data, $connection]);
            }
        } else {
            \Console::debug(
                'PROTOCOL_BACK_TO_FOUND: receive from (service:%s, node:%d, process:%d, askId:%d)'
                , $this->serviceRemotes[$service]->name
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
        }
    }

    protected function dispatchLocolOnService($handler, $connection, $header, $body)
    {
        //见权。
        if (!$handler->requeireAuthed() & $connection->getAuthed()) {
            $respHeader = Base::headerToResponse($header);
            $resp = new Base();
            $resp->setHeaders($respHeader);
            $resp->setCode(Error::AUTH_NOT_ALLOW);
            $connection->send($resp);
            \Console::debug(
                'AUTH_NOT_ALLOW: receive from (service:%s, node:%d, process:%d, askId:%d)'
                , $handler->getService()
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
        } elseif ($handler instanceof ConnectionInterface) {
            $handler->send($data);
            \Console::debug(
                'DISPATCH_REMOTE: receive from (service:%s, node:%d, process:%d, askId:%d)'
                , $handler->getService()
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
        } else {
            \Console::debug(
                'DISPATCH_LOCAL: receive from (service:%s, node:%d, process:%d, askId:%d)'
                , $handler->getService()
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
            $handler->dispatch($this, $connection, $header, $body);
        }
    }

    public function dispatcherLocal($connection, $header, $data)
    {
        $serviceId = $header['service'];
        $listen = $this->serviceAccepts[$serviceId];
        $body = substr($data, Setting::eof()['package_body_offset']);
        if ($header['flag'] & Base::PROTOCOL_IS_BROADCAST) {
            foreach ($listen as $handler) {
                $this->dispatchLocolOnService($handler, $connection, $header, $body);
            }
        } else {
            $handler = $listen->queue->shift();//队头出。
            $listen->queue->push($handler);//队尾进。
            $this->dispatchLocolOnService($handler, $connection, $header, $body);
        }
    }

    public function dispatchHarbor($connection, $header, $data)
    {
        //选择一个连接进行发送。。。
        $serviceId = $header['service'];
        $listen = $this->serviceRemotes[$serviceId];
        \Console::debug(
            'PROTOCOL_FORWARD_TO_HARBOR: receive from (service:%d, node:%d, process:%d, askId:%d)'
            , $header['service']
            , $header['fromNode']
            , $header['fromProcess']
            , $header['askId']
        );
        //广播或是单个。。。
    }

    public function dispatch($connection, $data)
    {
        //开始解析协议
        $header = Base::parseHeader($data);
        $type = $connection->getAuthed();
        $serviceId = $header['service'];
        $flag = $header['flag'];

        if ($flag & Base::PROTOCOL_IS_BACK) {
            //返回的协议，一定要标明Node. Service.
            if (isset($this->serviceRemotes[$serviceId])) {
                $this->onBack($connection, $header, $data);
            } else {
                \Console::debug(
                    'BACK_UNDEFINED: receive from (service:%d, node:%d, process:%d, askId:%d)'
                    , $header['service']
                    , $header['fromNode']
                    , $header['fromProcess']
                    , $header['askId']
                );
            }
        } else {
            //print_r($this->serviceAccepts);
            if (isset($this->serviceAccepts[$serviceId])) {
                //当前Node存在的
                $this->dispatcherLocal($connection, $header, $data);
            } elseif (isset($this->serviceRemotes[$serviceId])) {
                //转给港口服务
                $this->dispatchHarbor($connection, $header, $data);
            } else {
                $respHeader = Base::headerToResponse($header);
                $resp = new Base();
                $resp->setHeaders($respHeader);
                $resp->setCode(Error::PROTOCOL_NOT_FOUND);
                \Console::debug(
                    'PROTOCOL_NOT_FOUND: receive from (service:%d, node:%d, process:%d, askId:%d)'
                    , $header['service']
                    , $header['fromNode']
                    , $header['fromProcess']
                    , $header['askId']
                );
                $connection->send($resp);
            }
        }
    }
}