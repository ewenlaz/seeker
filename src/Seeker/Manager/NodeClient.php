<?php

namespace Seeker\Manager;

use Seeker\Client\TcpConnection;
use Seeker\Protocol\Json;
use Seeker\Protocol\Base;
use Seeker\Service\RemoteCall;

class NodeClient extends TcpConnection
{
    protected static $nodes = [];
    protected $dispatcher = null;
    protected $nodeId = 0;
    protected $authKey = '';

    public function setNodeId($nodeId)
    {
        static::$nodes[$nodeId] = $this;
        $this->nodeId = $nodeId;
        return $this;
    }

    public function getNodeId()
    {
        return $this->nodeId;
    }

    public function setAuthKey($key)
    {
        $this->authKey = $key;
        return $this;
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function onConnect()
    {
        //发送认证协议.

        $this->dispatcher->remoteCall('common.node.login')
            ->setToNode($this->nodeId)
            ->set('type', 'manager')
            ->set('authKey', $this->authKey)
            ->then(function($connection, $response) {
                echo '------ node is connect and authed' . PHP_EOL;
                $connection->setAuthed(static::AUTHED_COMMON | static::AUTHED_NODE);
            })
            ->sendTo($this);
    }

    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function onReceive($data)
    {
        $this->dispatcher->dispatch($this, $data);
    }

    public function __destruct()
    {
        unset(static::$nodes[$nodeId]);
    }

    public static function find($nodeId)
    {
        return isset(static::$nodes[$nodeId]) ? static::$nodes[$nodeId] : null;
    }

    public static function remove($nodeId)
    {
        if (isset(static::$nodes[$nodeId])) {
            unset(static::$nodes[$nodeId]);
            return true;
        }
        return false;
    }

    public static function boardcast(Base $resp)
    {
        foreach (static::$nodes as $node) {
            $resp->setToNode($node->getNodeId());
            $node->send($resp);
        }
    }

    public static function getAll()
    {
        return static::$nodes;
    }
}