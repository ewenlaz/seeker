<?php
namespace Seeker\Service\Dispatcher\Adapter;

use Seeker\Service\Dispatcher\AdapterInterface;
use Seeker\Standard\ConnectionInterface;
use Seeker\Server\Connection as ServerConnection;
use Seeker\Service\Dispatcher;
use Seeker\Protocol\Base;

class Connection implements AdapterInterface
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
        return $this->connection->getAuthed();
    }

    public function dispatch(Dispatcher $dispatch, ConnectionInterface $connection, $header, $body)
    {
        $req = new Base();
        $req->setHeaders($header);
        $req->setBodyStream($body);
        $req->then(function($data) use ($connection) {
            $connection->send($data);
        }, false);
        $req->sendTo($this->connection);
    }
}