<?php

namespace Seeker\Service\Node;

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
            $pushReq = $this->dispatcher->remoteCall('master.deploy.progress');
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

    public function startProcess()
    {
        //第一步找到目标目录
        $path = shared('setting')->get('deployPath');
        $process = $this->request->get('process');
        $version = $this->request->get('version');
        $startFile = $this->request->get('start');
        $path = $path . 'process/' . $process . '/' . $version . '/' . $startFile;
    }
}