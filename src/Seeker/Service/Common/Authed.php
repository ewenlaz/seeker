<?php
namespace Seeker\Service\Common;

use Seeker\Protocol\Error;

class Authed extends Base
{
    public function beforeDispatch()
    {
        if (!isset($this->connection->isAuth) || !$this->connection->isAuth) {
            $this->response->setCode(Error::AUTH_ERROR);
            $this->connection->send($this->response);
            $this->connection->close();
            return false;
        } else {
            return true;
        }
    }
}