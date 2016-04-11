<?php

namespace Seeker\Standard;

abstract class Connection
{
    const STATUS_CONNECT = 1;
    const STATUS_CLOSE = 2;
    const STATUS_TYPE_CHANGE = 3;

    const TYPE_GUEST = 0;
    const TYPE_MASTER = 1;
    const TYPE_SERVICE = 2;
    const TYPE_NODE_IN = 3;
    const TYPE_NODE_OUT = 4;
    const TYPE_TOOL = 5;

    public $isGuest = true;

    protected $statusCallback = [];
    protected $receiveCallback = [];
    protected $index = 0;
    protected $type = 0;

    protected $node = 0;
    protected $process = 0;

    abstract public function send($data);
    abstract public function close();

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setNode($node)
    {
        $this->node = $node;
    }

    public function getNode()
    {
        return $this->node;
    }

    public function setProcess($process)
    {
        $this->process = $process;
    }

    public function getProcess()
    {
        return $this->process;
    }


    public function setType($type)
    {
        if ($this->type) {
            return false;
        } else {
            $this->type = $type;
            if ($type) {
                $this->isGuest = false;
            }
            $this->onTypeChange();
            return true;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function onTypeChange()
    {
        foreach ($this->statusCallback as $callback) {
            call_user_func_array($callback, [$this, static::STATUS_TYPE_CHANGE]);
        }
    }

    public function onClose()
    {
        foreach ($this->statusCallback as $callback) {
            call_user_func_array($callback, [$this, static::STATUS_CLOSE]);
        }
    }

    public function onReceive($data)
    {
        foreach ($this->receiveCallback as $callback) {
            call_user_func_array($callback, [$this, $data]);
        }
    }

    public function onConnect()
    {
        foreach ($this->statusCallback as $callback) {
            call_user_func_array($callback, [$this, static::STATUS_CONNECT]);
        }
    }

    public function listenStatus($callback)
    {
        $this->statusCallback[] = $callback;
    }

    public function listenReceive($callback)
    {
        $this->receiveCallback[] = $callback;
    }
}