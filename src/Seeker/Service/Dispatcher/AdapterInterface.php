<?php

namespace Seeker\Service\Dispatcher;

use Seeker\Standard\ConnectionInterface;
use Seeker\Service\Dispatcher;
interface AdapterInterface
{
    public function requeireAuthed();
    public function getService();
    public function dispatch(Dispatcher $dispatch, ConnectionInterface $connection, $header, $body);
}