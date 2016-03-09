<?php

namespace Seeker\Protocol;

use Seeker\Standard\ConnectionInterface;

class Base
{
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

    protected $data = null;
    protected $streamBody = '';

    public function setHeaders($header)
    {
        $this->header = $header;
        return $this;
    }

    public function setBodyStream($body)
    {
        $this->streamBody = $body;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getBodyStream()
    {
        return $this->streamBody;
    }

    public function getToNode()
    {
        return $this->header['toNode'];
    }

    public function setToNode($val)
    {
        $this->header['toNode'] = $val;
        return $this;
    }

    public function getToProcess()
    {
        return $this->header['toProcess'];
    }

    public function setToProcess($val)
    {
        $this->header['toProcess'] = $val;
        return $this;
    }

    public function getAskId()
    {
        return $this->header['askId'];
    }

    public function setAskId($val)
    {
        $this->header['askId'] = $val;
        return $this;
    }

    public function getFromNode()
    {
        return $this->header['fromNode'];
    }

    public function setFromNode($val)
    {
        $this->header['fromNode'] = $val;
        return $this;
    }

    public function getFromProcess()
    {
        return $this->header['fromProcess'];
    }

    public function setFromProcess($val)
    {
        $this->header['fromProcess'] = $val;
        return $this;
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

    public function getFlag()
    {
        return $this->header['flag'];
    }

    public function setFlag($val)
    {
        $this->header['flag'] = $val;
        return $this;
    }

    public function getCode()
    {
        return $this->header['code'];
    }

    public function setCode($val)
    {
        $this->header['code'] = $val;
        return $this;
    }

    public function getStream()
    {
        $bodyStrem = $this->getBodyStream();
        return pack('nnnNnnLnl'
            , strlen($bodyStrem)
            , $this->getToNode()
            , $this->getToProcess()
            , $this->getAskId()
            , $this->getFromNode()
            , $this->getFromProcess()
            , $this->getService()
            , $this->getFlag()
            , $this->getCode()
        ) . $bodyStrem;
    }

    public function __toString()
    {
        return $this->getStream();
    }

    public function sendTo(ConnectionInterface $connection)
    {
        return $connection->send($this);
    }

    public function parseBody()
    {
        return $this->data = $this->parseBodyStream();
    }

    public function parseBodyStream()
    {
        return $this->streamBody;
    }

    public static function parseHeader($stream)
    {
        return unpack('nlen/nfromNode/nfromProcess/NaskId/ntoNode/ntoProcess/Lservice/nflag/lcode', $stream);
    }

    public static function headerToResponse($header)
    {

        return [
            'len' => $header['len'],
            'toNode' => $header['fromNode'],
            'toProcess' => $header['fromProcess'],
            'askId' => $header['askId'],
            'fromNode' => $header['toNode'],
            'fromProcess' => $header['toProcess'],
            'service' => $header['service'],
            'flag' => $header['flag'],
            'code' => $header['code']
        ];
    }
}