<?php
namespace Seeker\Service;

use Seeker\Protocol\Base\Setting;
use Seeker\Protocol\Base;

class RemoteCall
{
    protected static $listens = [];
    protected $request = null;

    public function __construct(Base $request)
    {
        $this->request = $request;
    }

    public function then(callable $call)
    {
        $this->request->setFlag($this->request->getFlag() | Dispatcher::PROTOCOL_MUST_BACK);
        echo 'listen Then:' . crc32($this->request->getService()) . '_' . $this->request->getAskId(). PHP_EOL;
        static::$listens[crc32($this->request->getService()) . '_' . $this->request->getAskId()] = $call;
        return $this;
    }

    public static function onBack($dispatcher, $connection, $response)
    {
        $key = $response->getService() . '_' . $response->getAskId();
        echo 'dispatch back Then:' . $key . PHP_EOL;
        if (isset(static::$listens[$key])) {
            call_user_func_array(static::$listens[$key], [$connection, $response]);
            unset(static::$listens[$key]);
        }
    }

    public function __call($method, $params)
    {
        $ret = call_user_func_array([$this->request, $method], $params);
        if ($ret === $this->request) {
            return $this;
        }
        return $ret;
    }
}