<?php
namespace Seeker\Service;

use Seeker\Protocol\Base\Setting;
use Seeker\Protocol\Base;
use Seeker\Protocol\Error;
use Seeker\Core\DI;
class Dispatcher
{   
    protected $serviceListeners = [];
    protected $proxyServiceListeners = [];

    public function listens($listens)
    {
        foreach ($listens as $service => $ctrl) {
            $id = crc32($service);
            if (isset($this->serviceListeners[$id])) {
                continue;
            }
            $listener = new Listener($service, Listener::ACCEPT);
            $listener->setRequest($ctrl['request']);
            $listener->setResponse($ctrl['response']);
            if (isset($ctrl['authed']) && $ctrl['authed']) {
                $listener->setAuthed((int)$ctrl['authed']);
            }
            $listener->setService($ctrl['service']);
            $this->pushListener($id, $listener);
        }
    }

    public function getServiceListeners()
    {
        return $this->serviceListeners;
    }

    public function pushListener($id, Listener $listener)
    {
        $this->serviceListeners[$id] = $listener;
    }

    public function getListener($service)
    {
        $id = crc32($service);
        return $this->getListenerById($id);
    }

    public function getListenerById($id)
    {
        return isset($this->serviceListeners[$id]) ? $this->serviceListeners[$id] : null;
    }

    public function pushProxyListener($id, Listener $listener)
    {
        $this->proxyServiceListeners[$id] = $listener;
    }


    public function getProxyListener($service)
    {
        $id = crc32($service);
        return $this->getProxyListenerById($id);
    }

    public function getProxyListenerById($id)
    {
        return isset($this->proxyServiceListeners[$id]) ? $this->proxyServiceListeners[$id] : null;
    }

    public function remoteCalls($listens)
    {
        foreach ($listens as $service => $ctrl) {
            $id = crc32($service);
            if (isset($this->serviceListeners[$id])) {
                continue;
            }

            $listener = new Listener($service, Listener::REMOTE_CALL);
            $listener->setRequest($ctrl['request']);
            $listener->setResponse($ctrl['response']);
            $this->pushListener($id, $listener);
        }
    }

    public function listenEvents($listens)
    {
        // foreach ($listens as $service => $ctrl) {
        //     $this->setServiceName($service);
        //     $this->listenEvents[crc32($service)] = $ctrl;
        // }
    }

    public function productEvents($listens)
    {
        // foreach ($listens as $service => $ctrl) {
        //     $this->setServiceName($service);
        //     $this->productEvents[crc32($service)] = $ctrl;
        // }
    }

    public function remoteCall($service)
    {
        $listener = $this->getListener($service);
        if ($listener && $listener->getType() == Listener::REMOTE_CALL) {
            $request = $listener->getRequest();
            $request = new $request;
            $request->setService($service);
            $askId = DI::get('askId')->create();
            $request->setAskId($askId);
            $remoteCall = new RemoteCall($request);
            
            \Console::debug(
                'REMOTE_CALL: to (service:%s, node:%d, process:%d, askId:%d)'
                , $listener->getName()
                , $request->getToNode()
                , $request->getToProcess()
                , $askId
            );

            return $remoteCall;
        } else {
            \Console::debug(
                'REMOTE_CALL: no found (service:%s)'
                , $service
            );
        }
    }

    public function dispatch($connection, $data)
    {
        //开始解析协议
        $header = Base::parseHeader($data);

        $listener = $this->getListenerById($header['service']);
        $flag = $header['flag'];

        //查询是不是Proxy...
        if (!$listener) {
            $listener = $this->getProxyListenerById($header['service']);
        }

        if ($listener && $flag & Base::PROTOCOL_IS_EVENT) {
            \Console::debug(
                'PROTOCOL_IS_EVENT: receive from (service:%s, node:%d, process:%d, askId:%d)'
                , $listener->getName()
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
        } elseif ($listener && $flag & Base::PROTOCOL_IS_BACK) {

            if ($listener->isProxy()) {
                \Console::debug(
                    'PROTOCOL_PROXY_BACK: receive from (service:%s, node:%d, process:%d, askId:%d)'
                    , $listener->getName()
                    , $header['fromNode']
                    , $header['fromProcess']
                    , $header['askId']
                );
            } else {
                \Console::debug(
                    'PROTOCOL_BACK: receive from (service:%s, node:%d, process:%d, askId:%d)'
                    , $listener->getName()
                    , $header['fromNode']
                    , $header['fromProcess']
                    , $header['askId']
                );
                $response = $listener->getResponse();
                $response = new $response;
                $response->setHeaders($header);
                $response->setBodyStream(substr($data, Setting::eof()['package_body_offset']));
                $response->parseBody();
                return RemoteCall::onBack($this, $connection, $response);
            }


        } elseif ($listener && $listener->checkAuth($connection->getAuthed())) {
            if ($listener->isProxy()) {
                \Console::debug(
                    'ACCEPT_PROXY: receive from (service:%s, node:%d, process:%d, askId:%d)'
                    , $listener->getName()
                    , $header['fromNode']
                    , $header['fromProcess']
                    , $header['askId']
                );
            } else {
                \Console::debug(
                    'ACCEPT_SELF: receive from (service:%s, node:%d, process:%d, askId:%d)'
                    , $listener->getName()
                    , $header['fromNode']
                    , $header['fromProcess']
                    , $header['askId']
                );
                //是发给自己的协议
                list($service, $method) = explode(':', $listener->getService());
                $request = $listener->getRequest();
                $request = new $request;
                $request->setHeaders($header);
                $request->setBodyStream(substr($data, Setting::eof()['package_body_offset']));
                $response = $listener->getResponse();
                $response = new $response;
                $respHeader = Base::headerToResponse($header);
                $response->setHeaders($respHeader);
                $service = new $service($this, $connection, $request, $response);
                $ret = $service->$method();
            }
        } elseif ($listener) {
            $respHeader = Base::headerToResponse($header);
            $resp = new Base();
            $resp->setHeaders($respHeader);
            $resp->setCode(Error::AUTH_NOT_ALLOW);
            $connection->send($resp);
            \Console::debug(
                'AUTH_NOT_ALLOW: receive from (service:%s, node:%d, process:%d, askId:%d)'
                , $listener->getName()
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
        } elseif ($flag & Base::PROTOCOL_IS_BACK) {
            \Console::debug(
                'PROTOCOL_NOT_FOUND but back: receive from (service:%d, node:%d, process:%d, askId:%d)'
                , $header['service']
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
        } else {
            $respHeader = Base::headerToResponse($header);
            $resp = new Base();
            $resp->setHeaders($respHeader);
            $resp->setCode(Error::PROTOCOL_NOT_FOUND);
            \Console::debug(
                'PROTOCOL_NOT_FOUND: receive from (service:%d, node:%d, process:%d, askId:%d)'
                , $header['service']
                , $header['fromNode']
                , $header['fromProcess']
                , $header['askId']
            );
            $connection->send($resp);
        }
    }
}