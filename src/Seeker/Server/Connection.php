<?php
namespace Seeker\Server;

use Seeker\Standard\ConnectionInterface;

class Connection implements ConnectionInterface
{
    protected $id = 0;
    protected $worker = null;
    protected $authed = 0;
    public $_callbacks = [];
    public $_lastCallback = 0;
    
    public function __construct($id, $worker)
    {
        $this->id = $id;
        $this->worker = $worker;
    }

    public function setAuthed($flag)
    {
        $this->authed = $flag;
        return $this;
    }

    public function getAuthed()
    {
        return $this->authed;
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