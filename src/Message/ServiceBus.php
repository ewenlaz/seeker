<?php

namespace Seeker\Message;

use SplObjectObject;
use Seeker\Proto\Header as ProtoHeader;
use Seeker\Proto\Manager as ProtoManager;
use Seeker\Proto\Wrapper;
use Seeker\Standard\Connection;

use StdClass;


class ServiceBus
{
    protected $node = 0;
    protected $process = 0;
    protected $protoAsks = [];
    protected $connections = [];
    protected $nodeOutConnections = [];
    protected $nodeInConnections = [];
    protected $processConnections = [];
    protected $masterConnection = null;

    public function __construct($node = 0, $process = 0)
    {
        $this->node = $node;
        $this->process = $process;
        $this->protoManager = new ProtoManager();
    }

    public function getNode()
    {
        return $this->node;
    }

    public function getProcess()
    {
        return $this->process;
    }

    public function getProtoManager()
    {
        return $this->protoManager;
    }

    public function listenConnection(Connection $connection)
    {
        $connection->listenStatus([$this, 'onConnectionStatus']);
        $connection->listenReceive([$this, 'onReceive']);
    }

    public function onConnectionStatus(Connection $connection, $status = 0, $data = null)
    {

        echo 'onConnectionStatus:'.$status . PHP_EOL;

        switch ($status) {
            case Connection::STATUS_CLOSE:
                //关闭后清除。
                switch ($connection->getType())
                {
                    case Connection::TYPE_MASTER:
                        $masterConnection = null;
                        break;
                    case Connection::TYPE_SERVICE:
                        unset($this->processConnections[$connection->getProcess()]);
                        break;
                    case Connection::TYPE_NODE_IN:
                        unset($this->nodeInConnections[$connection->getNode()]);
                        break;
                    case Connection::TYPE_NODE_OUT:
                        unset($this->nodeOutConnections[$connection->getNode()]);
                        break;
                    default:
                        unset($this->connections[$connection->getIndex()]);
                }

            break;

            case Connection::STATUS_CONNECT:
                //连接成功后关连
                $this->connections[$connection->getIndex()] = $connection;
            break;

            case Connection::STATUS_TYPE_CHANGE:
                //类型改变。。。
                switch ($connection->getType())
                {
                    case Connection::TYPE_MASTER:
                        $masterConnection = $connection;
                        break;
                    case Connection::TYPE_SERVICE:
                        $this->processConnections[$connection->getProcess()] = $connection;
                        break;
                    case Connection::TYPE_NODE_IN:
                        $this->nodeInConnections[$connection->getNode()] = $connection;
                        break;
                    case Connection::TYPE_NODE_OUT:
                        $this->nodeOutConnections[$connection->getNode()] = $connection;
                        break;
                }
                unset($this->connections[$connection->getIndex()]);
            break;
        }
    }

    public function sendRemoteCall(Connection $connection, Wrapper $proto, $callback = null)
    {
        if ($callback) {
            $callback = [$callback, microtime(true)];
            $this->protoAsks[$proto->askId] = $callback;
        }
        $connection->send($proto->getRequestStream());
    }

    public function sendServiceResponse(Connection $connection, Wrapper $proto)
    {
        $connection->send($proto->getResponseStream());
    }

    public function sendProxy($node = 0, $process = 0, Wrapper $proto)
    {
        //print_r($proto);
        $index = $node * 100000 + $process;
        $connection->send($proto->toStream());
    }

    public function dispacth(Connection $connection, Wrapper $proto, StdClass $handler)
    {
        if ($proto->flag & Wrapper::FLAG_EVENT) {
            if ($handler->action && $handler->event) {
                $proto->attachRequest(new $handler->event);
                list($class, $method) = explode(':', $handler->action);
                $service = new $class($this, $connection, $proto);
                if (method_exists($service, $method)) {
                    $service->$method();
                } else {
                    //方法不存在
                }
            } else {
                //不是事件无法分发。 
            }
        } elseif ($proto->flag & Wrapper::FLAG_RESPONSE) {

            if (isset($this->protoAsks[$proto->askId])) {
                $callback = $this->protoAsks[$proto->askId];
                unset($this->protoAsks[$proto->askId]);
                //异常处理。。。
                $proto->attachResponse(new $handler->response);
                $callback[0]($proto, $connection);
            } else {
                //\Console::debug('..... no callback');
            }
        } else {
            if ($handler->action && !$handler->event) {
                $proto->attachRequest(new $handler->request);
                $proto->attachResponse(new $handler->response);

                list($class, $method) = explode(':', $handler->action);
                $service = new $class($this, $connection, $proto);
                if (method_exists($service, $method)) {
                    $service->$method();
                } else {
                    //方法不存在
                }
            } else {
                //不是事件无法分发。 
            }
        }
    }

    public function onReceive(Connection $connection, $data)
    {
        $proto = new Wrapper($data);
        $handler = $this->protoManager->getHandler($proto->service);
        $proto->serviceName = $handler->serviceName;
        return $this->dispacth($connection, $proto, $handler);
        //$header = new \StdClass;
        // if (false && $header->isResponse()) {
        //     $this->onProtoResponse($connection, $header);
        // } elseif ($header->isEventSubscribe()) {
        //     $event = $this->protoManager->createEventSubscribe($header);
        // } else {
        //     $_service = $service = $this->protoManager->getServiceHandler($header->getService());
        //     list($service, $method) = explode(':', $service);
        //     $service = new $service($this, $connection, $header);
        //     // if (!method_exists($service, $method)) {
        //     //     throw new \Exception('method undefined:'. $_service, 1);
        //     // }
        //     // $ret = $service->$method();
        // }
    }
}
// include '../Core/Shared.php';
// include '../Proto/Header.php';
// include '../Proto/Base.php';
// include '../Proto/Json.php';
// include '../Proto/Manager.php';
// include '../Proto/Wrapper.php';
// include '../Service/Base.php';
// include '../Service/Test.php';
// include '../Service/Remote.php';
// $bus = new ServiceBus(1000, 100);
// $bus->getProtoManager()->registerService('user.login', 'Seeker\\Proto\\Json', 'Seeker\\Proto\\Json', 'Seeker\\Service\\Test:login');

// $bus->getProtoManager()->registerCall('user.login.ip', 'Seeker\\Proto\\Json', 'Seeker\\Proto\\Json');

// print_r($bus->getProtoManager());

// class Connection
// {
//     protected $name = '1111';
//     public $abc = '11111';
//     public static function test()
//     {

//     }

//     public function test2($a)
//     {
//         $this->name = $a;
//     }

//     public function send($name = '')
//     {

//     }
// }
// $connection = new Connection;


// $wrapper = new Wrapper(null, 'user.login');

// $data1 = $wrapper->toStream();



// $wrap2 = new Wrapper($data1 . json_encode(['a', 'b', 'c']));
// $data = $wrap2->toStream();

// $bus->onReceive($connection, $data);

// //exit;

// for ($j = 0; $j < 1; $j ++) {
//     $t = microtime(true);
//     for ($i = 0 ; $i < 100000; $i ++) {
//         $bus->onReceive($connection, $data);
//         //json_encode($array);
//         //$a = $connection->abc;
//         // $proto = new Proto($data);
//         // $proto->toNode = 1111;
//         // $proto->toProcess = 11111;
//         // $proto->fromNode = 1111;
//         // $proto->fromProcess = 1111;
//         // $proto->__toString();
//         //$connection->abc = 11111;
//         //json_decode($str, true);
//     }
//     $useTime[] = microtime(true) - $t;
// }

// echo (array_sum($useTime) / 1) . PHP_EOL;