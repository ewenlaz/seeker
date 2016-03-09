<?php

namespace Seeker\Service\Common;

use Seeker\Sharded;
use Seeker\Protocol\Error;

class Node extends Base
{
    //节点认证
    public function login()
    {
        //查找相应的Key.
        $key = $this->request->get('auth_key');

        $type = $this->request->get('type');

        $authKeys = shared('setting')->get('auth_keys');

        echo 'Auth......' . PHP_EOL;

        if ($authKeys && isset($authKeys[$key])) {
            $this->connection->setAuthed($authKeys[$key]);
            $this->connection->send($this->response);
        } else {
            $this->connection->send($this->response->setCode(Error::AUTH_ERROR));
            $this->connection->close();
        }
    }
}