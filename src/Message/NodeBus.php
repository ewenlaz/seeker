<?php

namespace Seeker\Message;

use Seeker\Proto\Manager as ProtoManager;
use Seeker\Proto\Wrapper;
use Seeker\Standard\Connection;


class NodeBus extends ServiceBus
{

    public function pushProcessConnection(Connection $connection, $process)
    {
        $this->processConnections[$process] = $connection;
    }

    public function pushNodeConnection(Connection $connection, $node)
    {
        $this->nodeConnections[$node] = $connection;
    }


    public function dispacthAcceptProxy(Connection $connection, Wrapper $proto)
    {
        //发生的消息
        //查询本地接受。
        //TO GUEST 。鉴权。
        if ($proto->flag & Wrapper::FLAG_BROADCAST) {
            foreach ($this->protoManager->getAllProcessByServiceId($proto->service) as $process) {
                $proto->toProcess = $process;
                $proto->toNode = $this->node;
                $this->sendProxy($this->processConnections[$process], $proto);
            }
            echo 'send to broadcast:' . $proto->service .PHP_EOL;
            foreach ($this->protoManager->getAllNodeByServiceId($proto->service) as $node) {
                $proto->toNode = $node;
                $proto->toProcess = 0;
                $this->sendProxy($this->nodeOutConnections[$node], $proto);
            }
        } else {
            $process = $this->protoManager->pickProcessByServiceId($proto->service);
            if ($process) {
                //处理协议。
                $proto->toNode = $this->node;
                $proto->toProcess = $process;
                $this->sendProxy($this->processConnections[$process], $proto);
                echo 'send to service:' . $proto->service .PHP_EOL;
            } else {
                //获取Node.
                $node = $this->protoManager->pickNodeByServiceId($proto->service);
                if ($node) {
                    $proto->toNode = $node;
                    $proto->toProcess = 0;
                    $this->sendProxy($this->nodeOutConnections[$node], $proto);
                    echo 'send to node:' . $proto->service .PHP_EOL;
                } else {
                    //没有服务能够受理。
                    echo 'not service:' . $proto->service .PHP_EOL;
                }
            }
        }
    }

    public function dispacthRemoteProxy(Connection $connection, Wrapper $proto)
    {
        if (isset($this->connections[$proto->socket])) {
            $this->connections[$proto->socket]->send($proto->toStream());
        } else {
            //回包错误.....
        }
    }

    public function dispacthEventProxy(Connection $connection, Wrapper $proto)
    {
        if ($proto->flag & Wrapper::FLAG_RESPONSE) {
            if ($connection->isGuest) {
                // proxy .. 特殊处理。
                return;
            }
            //已处理的event
            $lastServer = $this->protoManager->nodeEventSubscribes($proto->service, $proto->fromNode);
            echo 'Wrapper::FLAG_RESPONSE>'. $proto->service . PHP_EOL;
            foreach ($this->protoManager->processEventSubscribes($proto->service) as $server => &$listen) {
                $proto->toNode = $this->node;
                if ($listen->broadcast) {
                    foreach ($listen->processes as $process) {
                        $proto->toProcess = $process;
                        echo 'Event BroadCast Process To > ' . $process . PHP_EOL;
                        $this->sendProxy($this->processConnections[$process], $proto);
                    }
                } elseif (!array_key_exists($server, $lastServer)) {
                    $process = current($listen->processes);
                    if (!$process) {
                        reset($listen->processes);
                        $process = current($listen->processes);
                    }
                    $this->sendProxy($this->processConnections[$process], $proto);
                    next($listen->processes);
                    echo 'Event Send Process To > ' . $process . PHP_EOL;
                } else {
                    echo 'Event is subscribe > '. PHP_EOL;
                }
            }
        } else {
            //发生的协议。
            $proto->flag = $proto->flag | Wrapper::FLAG_RESPONSE;
            $proto->fromNode = $this->node;
            foreach ($this->protoManager->processEventSubscribes($proto->service) as $server => &$listen) {
                $proto->toNode = $this->node;
                if ($listen->broadcast) {
                    foreach ($listen->processes as $process) {
                        $proto->toProcess = $process;
                        $this->sendProxy($this->processConnections[$process], $proto);
                    }
                } else {
                    $process = current($listen->processes);
                    if (!$process) {
                        reset($listen->processes);
                        $process = current($listen->processes);
                    }
                    $this->sendProxy($this->processConnections[$process], $proto);
                    next($listen->processes);
                }
            }
            //获取有监听事件的Process 进行处理。
            foreach ($this->protoManager->nodeEventSubscribes($proto->service) as $node => $server) {
                $proto->toNode = $node;
                $proto->toProcess = 0;
                $this->sendProxy($this->nodeConnections[$node], $proto);
            }
        }
    }

    public function onReceive(Connection $connection, $data)
    {
        $proto = new Wrapper($data);
        //判断本地的处理
        if ($proto->flag & Wrapper::FLAG_EVENT) {
            $handler = $this->protoManager->getHandler($proto->service);
            //Node 暂不支持事件。
            $this->dispacthEventProxy($connection, $proto);
        } elseif ($proto->flag & Wrapper::FLAG_RESPONSE) {
            //考虑自己的回调？
            if (!$proto->socket) {
                $this->dispacth($connection, $proto);
            } else {
                $this->dispacthRemoteProxy($connection, $proto);
            }
        } else {
            $handler = $this->protoManager->getHandler($proto->service);
            if ($handler) {
                $proto->serviceName = $handler->serviceName;
                $this->dispacth($connection, $proto, $handler);
            } else {//是否要考虑广播问题
                $this->dispacthAcceptProxy($connection, $proto);
            }
        }
    }
}