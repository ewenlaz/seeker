<?php

namespace Seeker\Core;

use Phalcon\DiInterface;

class DI
{
    protected static $defaultDI = null;
    public static function setDefaultDI(DiInterface $di)
    {
        static::$defaultDI = $di;
    }

    public static function get($name)
    {
        return static::$defaultDI->get($name);
    }
}