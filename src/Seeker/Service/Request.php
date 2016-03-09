<?php

namespace Seeker\Service;


class Request
{
    protected $protocol = [];
    protected $data = '';
    public function __construct($data)
    {
        $this->protocol = unpack('nlen/NaskId/NfromService/NtoService', $data);
        $this->data = $data;
    }

    public function getAskId()
    {
        return $this->protocol['askId'];
    }

    public function getFromService()
    {
        return $this->protocol['fromService'];
    }

    public function getToService()
    {
        return $this->protocol['toService'];
    }

    public function unpack($protocol)
    {
        $this->data = new $protocol(substr($this->data, 14));
    }

    public function __get($name)
    {
        return $this->data->$name;
    }

    public function getData()
    {
        return $this->data;
    }
}