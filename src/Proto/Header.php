<?php

namespace Seeker\Proto;

class Header
{

    const FLAG_RESPONSE = 1; //是否是返回
    const FLAG_EVENT_PUBLISH = 2; //一定要返回
    const FLAG_EVENT_SUBSCRIBE = 4; //是否是事件
    const FLAG_BROADCAST = 8; //广播

    protected $header = [
        'len' => 0,
        'toNode' => 0,
        'toProcess' => 0,
        'askId' => 0,
        'fromNode' => 0,
        'fromProcess' => 0,
        'service' => 0,
        'flag' => 0,
        'code' => 0
    ];

    protected $data = '';

    public function __construct($data = '')
    {
        if ($data) {
            $this->header = unpack('nlen/nfromNode/nfromProcess/NaskId/ntoNode/ntoProcess/Lservice/nflag/lcode', $data);
            $this->data = substr($data, 24);
        }
    }

    public function getService()
    {
        return $this->header['service'];
    }

    public function setService($val)
    {
        if (!is_numeric($val)) {
            $val = crc32($val);
        }
        $this->header['service'] = $val;
        return $this;
    }

    public function getLen()
    {
        return $this->header['len'];
    }

    public function getData()
    {
        return $this->data;
    }

    public function attachProto(Base $data)
    {
        $this->data = $data->unpack($data);
        //$this->data = $data;
        //return $this;
    }

    public function getToNode()
    {
        return $this->header['toNode'];
    }

    public function setToNode($toNode)
    {
        $this->header['toNode'] = $toNode;
        return $this;
    }

    public function getToProcess()
    {
        return $this->header['toProcess'];
    }

    public function setToProcess($toProcess)
    {
        $this->header['toProcess'] = $toProcess;
        return $this;
    }

    public function getFromNode()
    {
        return $this->header['fromNode'];
    }

    public function setFromNode($fromNode)
    {
        $this->header['fromNode'] = $fromNode;
        return $this;
    }

    public function getFromProcess()
    {
        return $this->header['fromProcess'];
    }

    public function setFromProcess($fromProcess)
    {
        $this->header['fromProcess'] = $fromProcess;
        return $this;
    }

    public function getAskId()
    {
        return $this->header['askId'];
    }

    public function setAskId($askId)
    {
        $this->header['askId'] = $askId;
        return $this;
    }

    public function getFlag()
    {
        return $this->header['flag'];
    }

    public function getCode()
    {
        return $this->header['code'];
    }

    public function setCode($code)
    {
        $this->header['code'] = $code;
        return $this;
    }

    public function isResponse()
    {
        return !! ($this->header['flag'] & self::FLAG_RESPONSE);
    }

    public function isEventPublish()
    {
        return !! ($this->header['flag'] & self::FLAG_EVENT_PUBLISH);
    }

    public function isEventSubscribe()
    {
        return !! ($this->header['flag'] & self::FLAG_EVENT_SUBSCRIBE);
    }

    public function setHeaderByArray($header)
    {
        $this->header = $header;
    }

    public function reverse()
    {
        $header = new self;
        $header->setHeaderByArray([
            'len' => 0,
            'toNode' => $this->header['fromNode'],
            'toProcess' => $this->header['fromProcess'],
            'askId' => $this->header['askId'],
            'fromNode' => $this->header['toNode'],
            'fromProcess' => $this->header['toProcess'],
            'service' => $this->header['service'],
            'flag' => $this->header['flag'] | static::FLAG_RESPONSE,
            'code' => $this->header['code']
        ]);
        return $header;
    }

    public function toStream()
    {
        return pack('nnnNnnLnl'
            , strlen($this->data)
            , $this->getToNode()
            , $this->getToProcess()
            , $this->getAskId()
            , $this->getFromNode()
            , $this->getFromProcess()
            , $this->getService()
            , $this->getFlag()
            , $this->getCode()
        ) . $this->data;
    }
}