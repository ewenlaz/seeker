<?php


class Setting
{
    protected $settings = [];
    public function __construct($arr)
    {
        $this->settings = $arr;
    }
    public function get($key) {
        return $this->settings[$key];
    }
}

require __DIR__ . '/../src/start.php';