<?php

namespace Seeker\Service\Common;

use Seeker\Service\Base;
use Seeker\Standard\Connection;
use Seeker\Proto\Manager;


class Node extends Base
{
    const ERROR_TYPE_NOT_SUPPORT = 10001;
    public function login()
    {
        $reqData = $this->request->getData();
        switch ($reqData['type']) {
            case 'service':
                $this->connection->setNode($this->messageBus->getNode());
                $this->connection->setProcess((int)$reqData['process']);
                $this->connection->_server_ = $reqData['server'];
                $this->connection->setType(Connection::TYPE_SERVICE);
            break;
            case 'node':
                $this->connection->setNode((int)$reqData['node']);
                $this->connection->setType(Connection::TYPE_NODE_IN);
            break;
            case 'tool':
                $this->connection->setNode(1);
                $this->connection->setProcess(0);
                $this->connection->setType(Connection::TYPE_TOOL);
            break;
            case 'master':
                $this->connection->setNode((int)$reqData['node']);
                $this->connection->setType(Connection::TYPE_MASTER);
            break;
            default:
                $this->response->setCode(static::ERROR_TYPE_NOT_SUPPORT);
        }
        return $this->send();
        //向node 进行登录
    }

    public function serviceSync()
    {
        $services = $this->request->getData();
        foreach ($services as $service) {
            if (strpos($service[0], 'common.node.') === 0) {
                continue;
            }
            if ($service[1] == Manager::SERVICE_EVENT_SUBSCRIBE) {
                
                $this->protoManager->processEventSubscribeRegister(
                    $this->connection->getProcess()
                    , $this->connection->_server_
                    , $service[0]
                    , $service[1]
                    , $service[2]
                );
                echo sprintf('server: %s register subscribe>%s', $this->connection->_server_, $service[0]) . PHP_EOL;
            } else {
                echo sprintf('server: %s register proto>%s', $this->connection->_server_, $service[0]) . PHP_EOL;
                $this->protoManager->processServiceRegister($this->connection->getProcess(), $service[0], $service[1]);
            }
        }
        return $this->send();
    }
}