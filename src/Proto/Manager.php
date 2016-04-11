<?php

namespace Seeker\Proto;

use Seeker\Message\ServiceBus;
use StdClass;

class Manager
{

    const SERVICE_ACCEPT = 0;
    const SERVICE_REMOTE = 1;
    const SERVICE_EVENT_PUBLISH = 2;
    const SERVICE_EVENT_SUBSCRIBE = 3;

    public $protos = [];
    protected $processProtos = [];
    protected $nodeProtos = [];
    protected $nodeMaps = [];
    protected $nodeEventSubscribe = [];
    protected $processEventSubscribe = [];

    public function __construct()
    {
        $this->processProtos = [
            self::SERVICE_ACCEPT => [],
            self::SERVICE_REMOTE => [],
            self::SERVICE_EVENT_PUBLISH => []
        ];
        $this->nodeProtos = [
            self::SERVICE_ACCEPT => [],
            self::SERVICE_REMOTE => [],
            self::SERVICE_EVENT_PUBLISH => []
        ];
    }

    public function nodeServiceRegister($node = 0, $serviceName = '', $type = self::SERVICE_ACCEPT)
    {
        if (!isset($this->nodeMaps[$node])) {
            $this->nodeMaps[$node] = [
                self::SERVICE_ACCEPT => [],
                self::SERVICE_REMOTE => [],
                self::SERVICE_EVENT_PUBLISH => []
            ];
        }

        if (!isset($this->nodeMaps[$node][$type])) {
            //notice.....
            return false;
        }

        $id = crc32($serviceName);
        $protos = &$this->nodeProtos[$type];
        if (!isset($protos[$id])) {
            $protos[$id] = new StdClass;
            $protos[$id]->serviceName = $serviceName;
            $protos[$id]->nodes = [];
        }
        if (!isset($protos[$id]->nodes[$node])) {
            $protos[$id]->nodes = [];
        }
        $protos[$id]->nodes[$node] = $node;
        $this->nodeMaps[$node][$type][$id] = $serviceName;
        return true;
    }

    public function nodeServiceUnregister($node = 0, $serviceName = '', $type = self::SERVICE_ACCEPT)
    {
        $id = crc32($serviceName);
        //处理协议map...
        $protos = &$this->nodeProtos[$type];
        if (isset($protos[$id]) && isset($protos[$id]->nodes[$node])) {
            unset($protos[$id]->nodes[$node]);
        }
        if (count($protos[$id]->nodes) < 1) {
            unset($protos[$id]);
        }
        //处理nodemap
        if (isset($this->nodeMaps[$node][$id])) {
            unset($this->nodeMaps[$node][$id]);
            if (count($this->nodeMaps[$node]) < 1) {
                unset($this->nodeMaps[$node]);
            }
        }
    }

    public function pickNodeByServiceId($id = 0, $type = self::SERVICE_ACCEPT)
    {
        $protos = &$this->nodeProtos[$type];
        if (isset($protos[$id])) {
            $node = current($protos[$id]->nodes);
            if ($node) {
                next($protos[$id]->nodes);
                return $node;
            }
        }
        return false;
    }

    public function getAllNodeByServiceId($id = 0, $type = self::SERVICE_ACCEPT)
    {
        $protos = &$this->nodeProtos[$type];
        if (isset($protos[$id])) {
            return $protos[$id]->nodes;
        }
        return [];
    }

    //事件受理流程. 查询来源node->查询node不广播的server, 本地filter -> 广播
    //事件发生 -> 查询监听server 对象 process, 存在 处理， 查询node临听。广播
    //本地监听结构：   service->server -> [broadcast, processes => []]
    //远程结构 service ->nodes -> server.
    public function nodeEventSubscribeRegister($node = 0, $server = '', $serviceName = '', $broadcast = false)
    {
        //维度... serviceId => server => [broadcast => bool, nodes => [node]]
        //处理远程构构。
        $id = crc32($serviceName);
        if (!isset($this->nodeEventSubscribe[$id])) {
            $this->nodeEventSubscribe[$id] = [];
        }
        if (!isset($this->nodeEventSubscribe[$id][$node])) {
            $this->nodeEventSubscribe[$id][$node] = [];
        }
        $this->nodeEventSubscribe[$id][$node][$server] = $broadcast;
    }

    public function nodeEventSubscribeUnregister($node = 0, $server = '', $serviceName = '')
    {
        $id = crc32($serviceName);
        if (isset($this->nodeEventSubscribe[$id])
            && isset($this->nodeEventSubscribe[$id][$node])
            && isset($this->nodeEventSubscribe[$id][$node][$server])
        ) {
            unset($this->nodeEventSubscribe[$id][$node][$server]);
        }
    }

    public function nodeEventSubscribes($id = 0, $node = 0)
    {
        if (isset($this->nodeEventSubscribe[$id])) {
            if ($node) {
                if (isset($this->nodeEventSubscribe[$id][$node])) {
                    return $this->nodeEventSubscribe[$id][$node];
                }
            } else {
                return $this->nodeEventSubscribe[$id];
            }
            
        }
        return [];
    }


    public function processServiceRegister($process = 0, $serviceName = '', $type = self::SERVICE_ACCEPT)
    {
        $id = crc32($serviceName);
        $protos = &$this->processProtos[$type];
        if (!isset($protos[$id])) {
            $protos[$id] = new StdClass;
            $protos[$id]->serviceName = $serviceName;
            $protos[$id]->processes = [];
        }
        if (!isset($protos[$id]->processes[$process])) {
            $protos[$id]->processes = [];
        }
        $protos[$id]->processes[$process] = $process;
        return true;
    }

    public function processServiceUnregister($process = 0, $serviceName = '', $type = self::SERVICE_ACCEPT)
    {
        $id = crc32($serviceName);
        //处理协议map...
        $protos = &$this->processProtos[$type];
        if (isset($protos[$id]) && isset($protos[$id]->processes[$node])) {
            unset($protos[$id]->processes[$process]);
        }
        if (count($protos[$id]->processes) < 1) {
            unset($protos[$id]);
        }
    }

    public function pickProcessByServiceId($id = 0, $type = self::SERVICE_ACCEPT)
    {
        $protos = &$this->processProtos[$type];
        if (isset($protos[$id])) {
            $process = current($protos[$id]->processes);
            if ($process) {
                next($protos[$id]->processes);
                return $node;
            }
        }
        return false;
    }

    public function getAllProcessByServiceId($id = 0, $type = self::SERVICE_ACCEPT)
    {
        $protos = &$this->processProtos[$type];
        if (isset($protos[$id])) {
            return $protos[$id]->processes;
        }
        return [];
    }

    public function processEventSubscribeRegister($process = 0, $server = '', $serviceName = '', $broadcast = false)
    {
        //维度... serviceId => server => [broadcast => bool, nodes => [node]]
        //处理远程构构。
        $id = crc32($serviceName);
        if (!isset($this->processEventSubscribe[$id])) {
            $this->processEventSubscribe[$id] = [];
        }
        if (!isset($this->processEventSubscribe[$id][$server])) {
            $struct = new StdClass;
            $struct->broadcast = $broadcast;
            $struct->processes = []; 
            $this->processEventSubscribe[$id][$server] = $struct;
        }
        $this->processEventSubscribe[$id][$server]->processes[$process] = $process;
    }

    public function processEventSubscribeUnregister($process = 0, $server = '', $serviceName = '')
    {
        $id = crc32($serviceName);
        if (isset($this->processEventSubscribe[$id])
            && isset($this->processEventSubscribe[$id][$server])
            && isset($this->processEventSubscribe[$id][$server]->processes[$process])
        ) {
            unset($this->processEventSubscribe[$id][$server]->processes[$process]);
        }
    }

    public function processEventSubscribes($id = 0)
    {
        
        if (isset($this->processEventSubscribe[$id])) {
            return $this->processEventSubscribe[$id];
        }
        return [];
    }

    public function registerService($service, $request = null, $response = null, $action = null)
    {
        if (!$request || !$action) {
            throw new \Exception('service: '.$service.' must has request and action', 1);
        }
        $this->insertProto($service, $request, $response, null, $action);
    }

    public function registerCall($service, $request, $response = null)
    {
        if (!$request) {
            throw new \Exception('service: '.$service.' call must has request', 1);
        }
        $this->insertProto($service, $request, $response);
    }

    public function registerEventPublish($service, $event = null)
    {
        if (!$request) {
            throw new \Exception('service: '.$service.' event must has request', 1);
        }
        $this->insertProto($service, null, null, $event);
    }

    public function registerEventSubscribe($service, $event = null, $action = null, $broadcast = false)
    {
        if (!$event || !$action) {
            throw new \Exception('service: '.$service.' event must has request', 1);
        }
        $this->insertProto($service, null, null, $event, $action, $broadcast = false);
    }

    protected function insertProto($service, $request = null, $response = null, $event = null, $action = null, $broadcast = false)
    {
        $proto = new \Stdclass;
        $proto->serviceName = $service;
        $proto->request = $request;
        $proto->response = $response;
        $proto->action = $action;
        $proto->event = $event;
        $proto->broadcast = $broadcast;
        if ($event) {
            $proto->type = $action ? static::SERVICE_EVENT_SUBSCRIBE : static::SERVICE_EVENT_PUBLISH;
        } else {
            $proto->type = $action ? static::SERVICE_ACCEPT : static::SERVICE_REMOTE;
        }

        if ($request && !class_exists($request)) {
            throw new \Exception('service: '.$service.' request class not exists:'. $request, 1);
        }

        if ($response && !class_exists($response)) {
            throw new \Exception('service: '.$service.' response class not exists:'. $response, 1);
        }

        if ($event && !class_exists($event)) {
            throw new \Exception('service: '.$service.' event class not exists:'. $event, 1);
        }

        if ($action) {
            if (!strpos($action, ':')) {
                throw new \Exception('Dispatcher Adapter local service config not vaild:' . $action, 1);
            }
            list($class, $method) = explode(':', $action);
            if (!class_exists($class)) {
                throw new \Exception('service class undefined:'. $class, 1);
            }
        }
        $this->protos[crc32($service)] = $proto;
    }

    public function getHandler($service = 0)
    {
        print_r($this->protos);
        if (isset($this->protos[$service])) {
            return $this->protos[$service];
        }
        return false;
    }
}