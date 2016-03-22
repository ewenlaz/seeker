<?php

namespace Seeker\Service;

use Seeker\Client\Tcp as TcpConnection;
use Seeker\Protocol\Json;
use Seeker\Protocol\Base;
use Seeker\Service\RemoteCall;
use Seeker\Core\DI;

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
        DI::get('dispatcher')->remoteCall('common.node.login')
            ->setToNode($this->nodeId)
            ->set('type', 'common')
            ->set('authKey', $this->authKey)
            ->then(function($response, $connection) use ($that) {
                if (!$response->getCode()) {
                    \Console::debug('node connect success!---');
                    $connection->setAuthed(static::AUTHED_NODE);
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
        foreach (DI::get('dispatcher')->getServiceAccepts() as $id => $accepts) {
            if (in_array($accepts->name, $this->skipService)) {
                continue;
            }
            foreach ($accepts->queue as $queue) {
                $services[] = $queue->getService() . '|0|' . $queue->requeireAuthed();
            }
        }

        foreach (DI::get('dispatcher')->getServiceRemotes() as $id => $remote) {
            $services[] = $remote->name . '|1|0';
        }

        DI::get('dispatcher')->remoteCall('node.client.listens')
            ->set($services)
            ->then(function($response, $connection) {
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

    public function onReceive($data)
    {
        DI::get('dispatcher')->dispatch($this, $data);
    }
}