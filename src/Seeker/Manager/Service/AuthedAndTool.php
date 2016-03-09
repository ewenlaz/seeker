<?php

namespace Seeker\Manager\Service;

use Seeker\Sharded;
use Seeker\Service\Common\Authed;
use Seeker\Protocol\Error;

class AuthedAndTool extends Authed
{
    //节点工具认证
    public function beforeDispatch()
    {
        $ret = parent::beforeDispatch();
        if ($ret) {
            if (!isset($this->connection->type) || $this->connection->type !== 'tool') {
                $this->response->error(Error::AUTH_ERROR);
                $this->connection->send($this->response);
                $this->connection->close();
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
}