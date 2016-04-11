<?php

namespace Seeker\Service\Common;

use Seeker\Service\Base;
use Seeker\Proto\Manager;

class Process extends Base
{
    public function connect()
    {
        //向node 进行登录
        $req = [
            'type' => 'service',
            'key' => 'test001',
            'process' => 1001,
            'server' => 'main'
        ];
        
        $this->call('common.node.login')
            ->setData($req)
            ->then([$this, 'onLoginResult'])
            ->send();
    }

    public function onLoginResult($resp)
    {

        if ($resp->code) {
            echo '认证失败.....' . PHP_EOL;
            exit;
        } else {
            $services = [];
            foreach ($this->protoManager->protos as $handler) {
                $services[] = [$handler->serviceName, $handler->type, $handler->broadcast];
            }
            
            //同步Service ....
            $this->call('common.node.service_sync')
                ->setData($services)
                ->then([$this, 'onSyncResult'])
                ->send();
        }

    }

    public function onSyncResult()
    {
        //开始报告Service...
        echo '......' . PHP_EOL;
    }
}