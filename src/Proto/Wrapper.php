<?php

namespace Seeker\Proto;

use Swoole\Atomic;

class Wrapper
{

    const FLAG_EVENT = 1; //是否是事件
    const FLAG_RESPONSE = 2; //是否是返回
    const FLAG_REQUIRED = 4; //要求返回
    const FLAG_BROADCAST = 8; //广播

    protected static $askIdAtomic = null;

    public $serviceName = '';
    protected $_service;
    public $len = 0;
    public $toNode = 0;
    public $toProcess = 0;
    public $askId = 0;
    protected $_askId = 0;
    public $fromNode = 0;
    public $fromProcess = 0;
    public $service = 0;
    public $flag = 0;
    public $socket = 0;
    public $code = 0;
    protected $data = '';
    public $response = '';
    public $request = '';

    public function __construct($data = '', $serviceName = '')
    {
        if (!static::$askIdAtomic) {
            static::$askIdAtomic = new Atomic(1);
        }
        if ($data) {
            $header = unpack('nlen/nfromNode/nfromProcess/NaskId/ntoNode/ntoProcess/Lsocket/Lservice/nflag/lcode', $data);
            $this->len = $header['len'];
            $this->toNode = $header['toNode'];
            $this->toProcess = $header['toProcess'];
            $this->askId = $this->_askId = $header['askId'];
            $this->fromNode = $header['fromNode'];
            $this->fromProcess = $header['fromProcess'];
            $this->socket = $header['socket'];
            $this->service = $this->_service = $header['service'];
            $this->flag = $header['flag'];
            
            $this->code = $header['code'];
            $this->data = substr($data, 28);
        } elseif ($serviceName) {
            //askId...
            //Service...
            $this->serviceName = $serviceName;
            $this->service = $this->_service = crc32($serviceName);
            $this->askId = $this->_askId = static::$askIdAtomic->add();
        }
    }

    public function attachRequest(Base $data)
    {
        $this->request = $data;
        if ($this->data) {
            $this->request->setProtoStream($this->data);
            $this->data = '';
        }
    }

    public function attachResponse(Base $data)
    {
        $this->response = $data;
    }

    public function getRequestStream()
    {
        $data = $this->request->getProtoStream();

        return pack('nnnNnnLLnl'
            , strlen($data)
            , $this->toNode
            , $this->toProcess
            , $this->askId
            , $this->fromNode
            , $this->fromProcess
            , $this->socket
            , $this->_service
            , $this->flag
            , $this->request->getCode()
        ) . $data;
    }

    public function getResponseStream()
    {
        $data = $this->response->getProtoStream();
        return pack('nnnNnnLLnl'
            , strlen($data)
            , $this->fromNode
            , $this->fromProcess
            , $this->askId
            , $this->toNode
            , $this->toProcess
            , $this->socket
            , $this->_service
            , self::FLAG_RESPONSE
            , $this->response->getCode()
        ) . $data;
    }

    public function toStream()
    {
        return pack('nnnNnnLLnl'
            , strlen($this->data)
            , $this->toNode
            , $this->toProcess
            , $this->askId
            , $this->fromNode
            , $this->fromProcess
            , $this->socket
            , $this->_service
            , $this->flag
            , $this->code
        ) . $this->data;
    }

    public function __toString()
    {
        return $this->toStream();
    }
}