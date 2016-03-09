<?php

namespace Seeker\Manager\Service;

use Seeker\Sharded;
use Seeker\Manager\NodeClient;
use Seeker\Protocol\Error;
use Seeker\Service\Base;

class Deploy extends Base
{
    //节点认证
    public function progress()
    {
        //找到相应的Node...
        $nodeId = $this->request->getFromNode();
        $progress = $this->request->get('progress');
        $taskId = $this->request->get('taskId');

        print_r($this->request);

        echo 'Deploy Progress:' . sprintf('N:%d, T:%s, %d%%'
            , $nodeId
            , $taskId
            , $progress
        ) . PHP_EOL;
    }
}