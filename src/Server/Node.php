<?php

namespace Seeker\Server;

use Swoole\Server;
use Seeker\Message\NodeBus;


class Node
{
    protected $swServer = null;
    protected $connections = [];
    public function __construct()
    {
        $this->swServer = new Server(SOCKET_FILE, 0, SWOOLE_BASE, SWOOLE_SOCK_UNIX_STREAM);
        $this->swServer->set([
            'open_length_check'     => true,
            'package_length_type'   => 'n',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset'   => 28,       //第几个字节开始计算长度
            'package_max_length'    => 1024 * 8  //协议最大长度 8K
        ]);

        $this->swServer->listen('0.0.0.0', 9901, SWOOLE_SOCK_TCP);
        $this->swServer->on('connect', [$this, 'onConnect']);
        $this->swServer->on('receive', [$this, 'onReceive']);
        $this->swServer->on('close', [$this, 'onClose']);

        $this->messageBus = new NodeBus;

        $this->messageBus->protoManager
            ->registerService('common.node.login', 'Seeker\\Proto\\Json', 'Seeker\\Proto\\Json', 'Seeker\\Service\\Common\\Node:login');

        $this->messageBus->protoManager
            ->registerService('common.node.service_sync', 'Seeker\\Proto\\Json', 'Seeker\\Proto\\Json', 'Seeker\\Service\\Common\\Node:serviceSync');

        $this->swServer->start();
    }

    public function onConnect($serv, $fd, $fromId)
    {
        $connection = new Connection($serv, $fd, $fromId);
        $connection->setIndex(crc32('server_' . $fd));
        $info = $this->swServer->connection_info($fd, $fromId);
        //master . node . service, tool , proxy...
        $this->messageBus->listenConnection($connection);
        
        $this->connections[$fd] = $connection;
        $this->connections[$fd]->onConnect();

    }

    public function onReceive($serv, $fd, $fromId, $data)
    {
        $this->connections[$fd]->onReceive($data);
    }

    public function onClose($serv, $fd, $fromId)
    {
        $this->connections[$fd]->onClose();
        unset($this->connections[$fd]);
    }
}

new Node;