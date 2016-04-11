<?php
namespace Seeker\Server;

use Seeker\Standard\Connection as StandardConnection;
use Swoole\Server;

class Connection extends StandardConnection
{
    protected $swServer = null;
    protected $fd = 0;
    protected $fromId = 0;
    public function __construct(Server $swServer, $fd = 0, $fromId = 0)
    {
        $this->swServer = $swServer;
        $this->fd = $fd;
        $this->fromId = $fromId;
    }

    public function send($data)
    {
        $this->swServer->send($this->fd, $data, $this->fromId);
    }

    public function close()
    {
        $this->swServer->close($this->fd, true);
    }
}