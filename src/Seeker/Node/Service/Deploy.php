<?php

namespace Seeker\Node\Service;

use Seeker\Sharded;
use Seeker\Protocol\Error;
use Seeker\Service\Common\Base;
use Seeker\Manager\NodeClient;

class Deploy extends Base
{
    //节点认证
    public function push()
    {
        //找到相应的Node...
        echo 'get deploy task....' . PHP_EOL;
        //发送Progress....

        //Mask ... 需要做Http异步下载客户端。。。
        for ($i = 1; $i <= 10; $i ++) {
            $pushReq = $this->dispatcher->remoteCall('node.deploy.progress');
            $pushReq->set('progress', $i * 10);
            $pushReq->set('taskId', $this->request->get('taskId'));
            $pushReq->sendTo($this->connection);
            //sleep(1);
        }

        // $pushReq = $this->dispatcher->remoteCall('node.deploy.progress');
        // $pushReq->setToNode($nodeId);
        // $pushReq->set($this->request->get());
        // $pushReq->then(function($response) {
        //     echo 'remote back:' . $response;
        // });
    }

    public function remove()
    {
        $nodeId = $this->request->get('nodeId');
        echo 'master requet remove node : '. $nodeId . PHP_EOL; 
        //移除本地Node连接Id....
        NodeClient::remove($nodeId);
    }
}