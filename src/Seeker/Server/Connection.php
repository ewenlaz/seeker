<?php
namespace Seeker\Server;

use Seeker\Standard\ConnectionInterface;

class Connection implements ConnectionInterface
{
    protected $id = 0;
    protected $worker = null;
    public function __construct($id, $worker)
    {
        $this->id = $id;
        $this->worker = $worker;
    }

    public function send($data)
    {
        return $this->worker->getServer()->send($this->id, $data);
    }

    public function close()
    {
        return $this->worker->getServer()->close($this->id);
    }
}