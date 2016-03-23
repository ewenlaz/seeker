<?php
namespace User\Service;

use Seeker\Protocol\Error;
use Seeker\Service\Common\Base;

class Common extends Base
{
    //节点认证
    static $a = 0;
    public function login()
    {
        //找到相应的Node...
        static::$a ++;
        if (! (static::$a % 10)) {
            echo microtime(true) . static::$a . PHP_EOL;
        }
        // \Console::debug('this is user process login request !');
        // $this->response->set('loginTime', date('Y-m-d H:i:s'));
        // $this->response->sendTo($this->connection);
    }
}