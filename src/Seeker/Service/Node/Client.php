<?php

namespace Seeker\Service\Node;

use Seeker\Protocol\Error;
use Seeker\Service\Common\Base;
use Seeker\Service\Listener;

class Client extends Base
{
    public function listens()
    {
        $protocols = $this->request->get();
        print_r($protocols);
        foreach ($protocols as $protocol) {
            list($service, $type, $authed) = explode('|', $protocol);
            $listen = $this->dispatcher->getProxyListener($service);
            if ($listen) {
            	\Console::debug('proxy service register fail:%s', $service);
            } else {
            	$listen = new Listener($service, $type);
            	$listen->isProxy(true);
            	$listen->setAuthed($authed);
            	$this->dispatcher->pushProxyListener(crc32($service), $listen);
            }
        }
        $this->connection->send($this->response);
    }
}