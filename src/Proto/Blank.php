<?php

namespace Seeker\Proto;

class Blank extends Base
{
    public function setProtoStream($data = '')
    {
        $this->data = '';
    }

    public function getProtoStream()
    {
        return '';
    }
}