<?php
namespace Seeker\Server\Tcp;

interface TcpWorkerInterface
{
    public function onConnect($connection);
    public function onClose($connection);
    public function onReceive($connection, $data);
}