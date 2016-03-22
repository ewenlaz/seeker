<?php
namespace Seeker\Service\Dispatcher\Adapter;

use Seeker\Service\Dispatcher\AdapterInterface;
use Seeker\Standard\ConnectionInterface;
use Seeker\Client\Tcp;
use Seeker\Service\Dispatcher;

class TcpClient extends Tcp implements AdapterInterface
{
    protected $serviceName = '';
    protected $connection = null;

    public function __construct($name, ConnectionInterface $connection)
    {
        $this->serviceName = $name;
        $this->connection = $connection;
    }

    public function getService()
    {
        return $this->serviceName;
    }
    
    public function requeireAuthed()
    {
        return $this->authed;
    }

    public function dispatch(Dispatcher $dispatch, ConnectionInterface $connection, $header, $data)
    {
        $this->connection->send($data);
    }
}