<?php

namespace Seeker\Proto;

class Json extends Base
{
    public function setProtoStream($data = '')
    {
        $this->data = json_decode($data, true);
    }

    public function getProtoStream()
    {
        return json_encode($this->data);
    }
}