<?php

namespace Seeker\Core;

use Phalcon\DI as PhalconDI;

class DIFactory extends PhalconDI
{
    public function __construct()
    {
        Di::setDefaultDI($this);
    }
}