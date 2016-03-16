<?php
namespace Seeker\Service;

use Seeker\Protocol\Base\Setting;
use Seeker\Protocol\Base;
use Seeker\Protocol\Error;
use Seeker\Core\DI;
class Dispatcher
{   
    protected $listens = [];
    protected $remoteCalls = [];
    protected $listenEvents = [];
    protected $productEvents = [];


    public function listens($listens)
    {
        foreach ($listens as $service => $ctrl) {
            echo 'Service:'. crc32($service) . PHP_EOL;
            $this->listens[crc32($service)] = $ctrl;
        }
    }

    public function remoteCalls($listens)
    {
        foreach ($listens as $service => $ctrl) {
            echo 'Service:'. crc32($service) . PHP_EOL;
            $this->remoteCalls[crc32($service)] = $ctrl;
        }
    }

    public function listenEvents($listens)
    {
        foreach ($listens as $service => $ctrl) {
            echo 'Service:'. crc32($service) . PHP_EOL;
            $this->listenEvents[crc32($service)] = $ctrl;
        }
    }

    public function productEvents($listens)
    {
        foreach ($listens as $service => $ctrl) {
            echo 'Service:'. crc32($service) . PHP_EOL;
            $this->productEvents[crc32($service)] = $ctrl;
        }
    }

    public function remoteCall($service)
    {
        if (isset($this->remoteCalls[crc32($service)])) {

            $remote = $this->remoteCalls[crc32($service)];
            $request = new $remote['request'];
            $request->setService($service);
            $askId = DI::get('askId')->create();
            $request->setAskId($askId);
            $remoteCall = new RemoteCall($request);
            \Console::debug('remove call:'.$service . ', AskID:' . $askId);
            return $remoteCall;
        } else {
            echo 'remote service not listen....:' . $service . PHP_EOL;
            //throw new \Exception("remote service not listen....", 1);
        }
    }

    public function dispatch($connection, $data)
    {
        //开始解析协议

        // $base = Base();
        // $base->setStream($data);
        // $service = $base->getHeader('service');

        $header = Base::parseHeader($data);

        $service = $header['service'];

        $flag = $header['flag'];

        $listener = null;
        if ($flag & Base::PROTOCOL_IS_EVENT) {
            if (isset($this->listenEvents[$service])) {
                $listener = $this->listenEvents[$service];
            }
        } else {
            if ($flag & Base::PROTOCOL_IS_BACK) {
                if (isset($this->remoteCalls[$service])) {
                    $listener = $this->remoteCalls[$service];
                    //找到被监听的RemoteCall....
                    $response = $listener['response'];
                    $response = new $response();
                    $response->setHeaders($header);
                    $response->setBodyStream(substr($data, Setting::eof()['package_body_offset']));
                    $response->parseBody();
                    return RemoteCall::onBack($this, $connection, $response);
                } else {
                    echo 'callback not found' . PHP_EOL;
                }              
            } else {
                if (isset($this->listens[$service])) {
                    $listener = $this->listens[$service];
                }
            }
        }

        if ($listener) {

            $listener = $this->listens[$service];

            //验证权限。。。。
            if (!isset($listener['authed']) || !$listener['authed'] || $connection->getAuthed() & $listener['authed']) {
                list($service, $method) = explode(':', $listener['service']);

                $request = $listener['request'];
                $request = new $request();
                $request->setHeaders($header);
                $request->setBodyStream(substr($data, Setting::eof()['package_body_offset']));

                $response = $listener['response'];

                $response = new $response();
                $respHeader = Base::headerToResponse($header);

                $response->setHeaders($respHeader);

                $service = new $service($this, $connection, $request, $response);
                $ret = $service->$method();
            } else {
                $respHeader = Base::headerToResponse($header);
                $resp = new Base();
                $resp->setCode(Error::AUTH_NOT_ALLOW);
                $connection->send($resp);
                echo 'AUTH_NOT_ALLOW' . PHP_EOL;
            }
        } else {
            $respHeader = Base::headerToResponse($header);
            $resp = new Base();
            $resp->setHeaders($respHeader);
            $resp->setCode(Error::PROTOCOL_NOT_FOUND);
            $connection->send($resp);
        }
    }
}