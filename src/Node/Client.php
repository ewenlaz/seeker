<?php
namespace Seeker\Node;

use Seeker\Standard\Connection;
use Swoole\Client as SwooleClient;

class Client extends Connection
{
    protected $host = '';
    protected $port = 0;
    protected $swClient = null;
    public function __construct($host = '', $port = 0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->swClient = new SwooleClient($port ? SWOOLE_SOCK_TCP : SWOOLE_SOCK_UNIX_STREAM, SWOOLE_SOCK_ASYNC);
        $this->swClient->set([
            'open_length_check'     => true,
            'package_length_type'   => 'n',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset'   => 28,       //第几个字节开始计算长度
            'package_max_length'    => 1024 * 8  //协议最大长度 8K
        ]);
        $this->swClient->on('connect', [$this, 'onSwooleConnect']);
        $this->swClient->on('receive', [$this, 'onSwooleReceive']);
        $this->swClient->on('close', [$this, 'onSwooleClose']);
        $this->swClient->on('error', [$this, 'onSwooleError']);

    }

    public function onSwooleConnect($cli)
    {
        echo 'swoole server connection_aborted(oid)' . PHP_EOL;
        $this->onConnect();
    }

    public function onSwooleReceive($cli, $data)
    {
        $this->onReceive($data);
    }

    public function onSwooleClose($cli)
    {
        echo 'swoole server close' . PHP_EOL;
    }

    public function onSwooleError($cli)
    {
        echo 'swoole server error' . PHP_EOL;
    }

    public function send($data)
    {
        echo $data . PHP_EOL;
        $this->swClient->send($data);
    }

    public function close()
    {
        $this->swClient->close();
    }

    public function connect()
    {
        $res = $this->swClient->connect($this->host, $this->port, -1);
    }
}