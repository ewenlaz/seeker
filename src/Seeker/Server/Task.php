<?php
namespace Seeker\Server;
use Seeker\Server\Standard\TaskInterface;
use Seeker\Server\Standard\WorkerInterface;
use Seeker\Server\Standard\WorkerBaseTrait;
class Task implements WorkerInterface,TaskInterface
{
    use WorkerBaseTrait;
    public function onTask()
    {

    }

    public function onPipeMessage()
    {
        
    }

    public function onStart()
    {

    }

    public function onStop()
    {
        
    }
}