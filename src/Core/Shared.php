<?php

namespace Seeker\Core;

class Shared
{
    protected static $shares = [];
    public static function get($key = '')
    {
        if (isset(static::$shares[$key])) {
            return static::$shares[$key];
        } else {
            return null;
        }
    }

    public static function set($key = '', $val = null)
    {
        static::$shares[$key] = $val;
    }
}