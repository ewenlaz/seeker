<?php

namespace Seeker\Manager\Service;

use Seeker\Sharded;
use Seeker\Manager\NodeClient;
use Seeker\Protocol\Error;

class Tool extends AuthedAndTool
{
    //节点认证
    public function push()
    {
        //找到相应的Node...
        $nodeId = $this->request->get('nodeId');

        $pushReq = $this->dispatcher->remoteCall('manager.deploy.push');
        $pushReq->setToNode($nodeId);
        $pushReq->set($this->request->get());
        $pushReq->then(function($response) {
            echo 'remote back:' . $response;
        });

        $node = NodeClient::find($nodeId);
        if (!$node && $node->isAuth) {
            $this->response->setCode(Error::NODE_NOT_FOUND);
        } else {
            $pushReq->sendTo($node);
        }
        $this->connection->send($this->response);
    }

    public function pushResponse()
    {
        if ($this->request->getCode() !== 0) {
            //$this->connection->close(); //发送失败。
        } else {
            //发送成功
        }
    }
}