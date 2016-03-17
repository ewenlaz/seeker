<?php

namespace Seeker\Service\Master;

use Seeker\Service\Common\Base;
use Seeker\Service\NodeClient;
use Seeker\Protocol\Error;
use Seeker\Protocol\Base\Setting;
use Seeker\Standard\ConnectionInterface;

class Node extends Base
{
    const ERROR_NODE_EXISTS = 10001;
    const ERROR_NODE_NOT_EXISTS = 10002;
    public function add()
    {
        $ip = $this->request->get('ip');
        $port = $this->request->get('port');
        $nodeId = $this->request->get('nodeId');
        $authedKey = $this->request->get('authedKey');

        //判断是存在。。。。
        $node = NodeClient::find($nodeId);
        if ($node) {
            $this->response->setCode(static::ERROR_NODE_EXISTS);
            $this->connection->send($this->response);
        } else {
            //开始连接Nodes......
            $nodeClient = new NodeClient($ip, $port, Setting::eof());
            $nodeClient
                ->setDispatcher($this->dispatcher)
                ->setNodeId($nodeId)
                ->setAuthKey($authedKey)
                ->connect();
            $this->connection->send($this->response);
        }
    }

    public function remove()
    {
        $nodeId = $this->request->get('nodeId');
        $node = NodeClient::find($nodeId);

        if (!$node) {
            $this->response->setCode(static::ERROR_NODE_NOT_EXISTS);
            $this->connection->send($this->response);
        } else {
            //广播给所有的节点。。。
            NodeClient::remove($nodeId);
            $pushReq = $this->dispatcher->remoteCall('node.deploy.remove');
            $pushReq->set('nodeId', $nodeId);
            NodeClient::boardcast($pushReq);
        }
    }

    public function lists()
    {
        $nodes = [];
        foreach (NodeClient::getAll() as $node) {
            $temp = [
                'nodeId' => $node->getNodeId(),
                'port' => $node->getPort,
                'host' => $node->getHost()
            ];
            $nodes[] = $temp;
        }
        $this->response->set('nodes', $nodes);
        $this->connection->send($this->response);
    }


    public function deploy()
    {
        //找到相应的Node...
        $nodeId = $this->request->get('nodeId');

        $pushReq = $this->dispatcher->remoteCall('node.deploy.push');
        $pushReq->setToNode($nodeId);
        $pushReq->set($this->request->get());
        $pushReq->then(function($response) {
            echo 'remote back:' . $response;
        });

        $node = NodeClient::find($nodeId);
        if (!$node || !($node->getAuthed() & ConnectionInterface::AUTHED_NODE)) {
            $this->response->setCode(Error::NODE_NOT_FOUND);
        } elseif ($node) {
            $pushReq->sendTo($node);
        }
        $this->connection->send($this->response);
    }

    public function startProcess()
    {
        $nodeId = $this->request->get('nodeId');

        $node = NodeClient::find($nodeId);
        if (!$node || !($node->getAuthed() & ConnectionInterface::AUTHED_NODE)) {
            $this->response->setCode(static::ERROR_NODE_NOT_EXISTS);
        } elseif ($node) {
            $pushReq = $this->dispatcher->remoteCall('node.deploy.start_process');
            $pushReq->setToNode($nodeId);
            $pushReq->set($this->request->get());
            $pushReq->then(function($response) {
                echo 'remote back:' . $response;
            });

            $pushReq->sendTo($node);
        }
        $this->connection->send($this->response);
    }

    public function stopProcess()
    {
        $nodeId = $this->request->get('nodeId');

        $node = NodeClient::find($nodeId);
        if (!$node || !($node->getAuthed() & ConnectionInterface::AUTHED_NODE)) {
            $this->response->setCode(static::ERROR_NODE_NOT_EXISTS);
        } elseif ($node) {
            $pushReq = $this->dispatcher->remoteCall('node.deploy.stop_process');
            $pushReq->setToNode($nodeId);
            $pushReq->set($this->request->get());
            $pushReq->then(function($response) {
                echo 'remote back:' . $response;
            });

            $pushReq->sendTo($node);
        }
        $this->connection->send($this->response);
    }

    public function removeProcess()
    {
        $nodeId = $this->request->get('nodeId');

        $node = NodeClient::find($nodeId);
        if (!$node || !($node->getAuthed() & ConnectionInterface::AUTHED_NODE)) {
            $this->response->setCode(static::ERROR_NODE_NOT_EXISTS);
        } elseif ($node) {
            $pushReq = $this->dispatcher->remoteCall('node.deploy.remove_process');
            $pushReq->setToNode($nodeId);
            $pushReq->set($this->request->get());
            $pushReq->then(function($response) {
                echo 'remote back:' . $response;
            });

            $pushReq->sendTo($node);
        }
        $this->connection->send($this->response);
    }

    public function startService()
    {
        $nodeId = $this->request->get('nodeId');

        $node = NodeClient::find($nodeId);
        if (!$node || !($node->getAuthed() & ConnectionInterface::AUTHED_NODE)) {
            $this->response->setCode(static::ERROR_NODE_NOT_EXISTS);
        } elseif ($node) {
            $pushReq = $this->dispatcher->remoteCall('node.deploy.start_service');
            $pushReq->setToNode($nodeId);
            $pushReq->set($this->request->get());
            $pushReq->then(function($response) {
                echo 'remote back:' . $response;
            });

            $pushReq->sendTo($node);
        }
        $this->connection->send($this->response);
    }

    public function stopService()
    {
        $nodeId = $this->request->get('nodeId');

        $node = NodeClient::find($nodeId);
        if (!$node || !($node->getAuthed() & ConnectionInterface::AUTHED_NODE)) {
            $this->response->setCode(static::ERROR_NODE_NOT_EXISTS);
        } elseif ($node) {
            $pushReq = $this->dispatcher->remoteCall('node.deploy.stop_service');
            $pushReq->setToNode($nodeId);
            $pushReq->set($this->request->get());
            $pushReq->then(function($response) {
                echo 'remote back:' . $response;
            });

            $pushReq->sendTo($node);
        }
        $this->connection->send($this->response);
    }
}