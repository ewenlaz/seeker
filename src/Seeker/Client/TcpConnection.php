<?php
namespace Seeker\Client;

use Seeker\Standard\ConnectionInterface;
use Swoole\Client;

class TcpConnection implements ConnectionInterface
{
    protected $client = null;
    protected $host = '';
    protected $port = 0;
    public function __construct($host = '', $port = 0)
    {
        $this->client = new Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->on('connect', [$this, 'onSwooleConnect']);
        $this->client->on('close', [$this, 'onSwooleClose']);
        $this->client->on('receive', [$this, 'onSwooleReceive']);
        $this->client->on('error', [$this, 'onSwooleError']);
        $this->host = $host;
        $this->port = $port;
    }

    public function connect()
    {
        $this->client->connect($this->host, $this->port);
    }

    public function onConnect()
    {

    }

    public function onClose()
    {

    }

    public function onError()
    {

    }

    public function onReceive($data)
    {

    }

    public function onSwooleReceive($client, $data)
    {
        $this->onReceive($data);
    }

    public function onSwooleConnect($client)
    {
        $this->onConnect();
    }

    public function onSwooleClose($client)
    {
        $this->onClose();
    }

    public function onSwooleError($client)
    {
        $this->onError();
    }

    public function send($data)
    {
        return $this->client->send($data);
    }

    public function close()
    {
        return $this->client->close();
    }
}