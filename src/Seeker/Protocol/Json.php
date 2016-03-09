<?php

namespace Seeker\Protocol;

use Seeker\Protocol\Base;

class Json extends Base
{

    public function get($name = null)
    {
        if ($this->data && isset($this->data->$name)) {
            return $this->data->$name;
        } elseif ($name === null) {
            return $this->data;
        } else {
            return null;
        }
    }

    public function set($name = null, $val = null)
    {
        if (!$this->data) {
            $this->data = new \StdClass;
        }
        if (is_array($name) || is_object($name)) {
            foreach ($name as $key => $val) {
                $this->data->$key = $val;
            }
        } elseif ($name) {
            $this->data->$name = $val;
        }
        return $this;
    }


    public function getBodyStream()
    {
        return json_encode($this->data);
    }

    public function parseBodyStream()
    {
        return json_decode($this->streamBody);
    }
}