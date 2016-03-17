<?php

namespace Seeker\Service;

use Seeker\Client\TcpConnection;
use Seeker\Protocol\Json;
use Seeker\Protocol\Base;
use Seeker\Service\RemoteCall;

class ConnectClient extends TcpConnection
{
    protected $dispatcher = null;
    protected $nodeId = 0;
    protected $authKey = '';
    protected $skipService = ['common.node.login', 'node.client.listens'];

    public function setNodeId($nodeId)
    {
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

        $that = $this;
        $this->dispatcher->remoteCall('common.node.login')
            ->setToNode($this->nodeId)
            ->set('type', 'common')
            ->set('authKey', $this->authKey)
            ->then(function($connection, $response) use ($that) {
                if (!$response->getCode()) {
                    \Console::debug('node connect success!---');
                    $connection->setAuthed(static::AUTHED_COMMON);
                    //发送协议注册
                    $that->syncServiceListener();
                } else {
                    $connection->close();
                }
            })
            ->sendTo($this);
    }
    
    public function syncServiceListener()
    {

        \Console::debug('start to sync service listens!');
        $services = [];
        foreach ($this->dispatcher->getServiceListeners() as $listener) {
            if (in_array($listener->getName(), $this->skipService)) {
                continue;
            }
            $services[] = $listener->getName() . '|' . $listener->getType() . '|' . $listener->getAuthed();
        }

        $this->dispatcher->remoteCall('node.client.listens')
            ->set($services)
            ->then(function($connection, $response) {
                \Console::debug(
                    'sync service listener: (code:%d)'
                    , $response->getCode()
                );
            })
            ->sendTo($this);
    }

    public function onClose()
    {

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
}