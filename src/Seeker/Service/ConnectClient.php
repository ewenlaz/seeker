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
        $this->dispatcher->remoteCall('common.node.login')
            ->setToNode($this->nodeId)
            ->set('type', 'common')
            ->set('authKey', $this->authKey)
            ->then(function($connection, $response) {
                if (!$response->getCode()) {
                    \Console::debug('node connect success!');
                    $connection->setAuthed(static::AUTHED_COMMON);
                } else {
                    $connection->close();
                }
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