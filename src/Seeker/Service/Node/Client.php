<?php

namespace Seeker\Service\Node;

use Seeker\Protocol\Error;
use Seeker\Service\Common\Base;
use Seeker\Service\Dispatcher\Adapter\Connection as AdapterConnection;


class Client extends Base
{
    public function listens()
    {
        $protocols = $this->request->get();
        print_r($protocols);

        foreach ($protocols as $protocol) {
            
            list($service, $type, $authed) = explode('|', $protocol);
            
            switch ($type) {
                case '0':
                    $adapter = new AdapterConnection($service, $this->connection);
                    $this->dispatcher->addAcceptAdapter($service, $adapter);
                break;
                case '1':
                    $this->dispatcher->addRemoteServiceCall($service);
                break;
            }
        }
        $this->response->sendTo($this->connection);
    }
}