<?php

namespace Seeker\Service\Common;

use Seeker\Sharded;
use Seeker\Protocol\Error;
use Seeker\Core\DI;
class Node extends Base
{
    //节点认证
    public function login()
    {
        //查找相应的Key.
        $key = $this->request->get('authKey');

        $type = $this->request->get('type');

        $authKeys = DI::get('auth_keys');

        if ($authKeys && isset($authKeys[$key])) {
            $this->connection->setAuthed($authKeys[$key]);
            \Console::debug('login success');
            $this->response->sendTo($this->connection);

        } else {
            \Console::debug('login fail:auth error');
            $this->response->setCode(Error::AUTH_ERROR);
            $this->response->sendTo($this->connection);
            $this->connection->close();
        }
    }
}