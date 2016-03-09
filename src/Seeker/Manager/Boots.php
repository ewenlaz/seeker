<?php
namespace Seeker\Manager;

use Seeker\Protocol\Base\Setting;
use Seeker\Server\Base;
use Seeker\Server\Tcp\Listener;
class Boots
{
    public function start()
    {
        $server = new Base;
        //$server->addTask(new Seeker\Server\Task, 10);
        //$server->addProcess(new Process, 10);

        $listener = new Listener(9901);
        $setting = Setting::eof();
        $setting['worker_num'] = 1;
        $listener->setting($setting);
        
        $listener->setWorker(new Worker);

        $server->addListener($listener);

        $server->start();
    }
}