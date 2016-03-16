<?php
namespace Seeker\Protocol;

use Swoole\Atomic;

class AskId
{
    protected $atomic = null;

    public function __construct()
    {
        $this->atomic = new Atomic(100000000);
    }

    public function create()
    {
        $id = $this->atomic->add();
        if ($id >= 900000000) {
            $this->cmpset($id, 100000000);
        }
        return $id;
    }
}