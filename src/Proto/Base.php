<?php

namespace Seeker\Proto;

abstract class Base
{
    protected $data = '';
    protected $code = 0;

    abstract public function setProtoStream($data = '');

    abstract public function getProtoStream();

    public function __toStream()
    {
        return $this->getProtoStream();
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode()
    {
        $this->code = 0;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}