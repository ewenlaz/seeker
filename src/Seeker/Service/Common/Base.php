<?php

namespace Seeker\Service\Common;

use Seeker\Service\Dispatcher;

class Base
{
    protected $dispatcher = null;
    protected $request = null;
    protected $response = null;
    protected $connection = null;
    public function __construct($dispatcher, $connection, $request, $response)
    {
        $this->dispatcher = $dispatcher;
        $this->request = $request;
        $this->request->parseBody();
        $response->setFlag($response->getFlag() | Dispatcher::PROTOCOL_IS_BACK);
        $this->response = $response;

        $this->connection = $connection;
    }
}