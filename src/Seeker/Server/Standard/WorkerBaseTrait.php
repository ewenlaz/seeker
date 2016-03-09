<?php
namespace Seeker\Server\Standard;

trait WorkerBaseTrait
{
    protected $id = 0;
    protected $pid = 0;
    protected $server = null;
    public function getPid(){
        return $this->pid;
    }
    public function setPid($pid){
        $this->pid = $pid;
    }
    public function setId($id){
        $this->id = $id;
    }
    public function getId(){
        return $this->id;
    }

    public function setServer($server){
        $this->server = $server;
    }
    public function getServer(){
        return $this->server;
    }
}