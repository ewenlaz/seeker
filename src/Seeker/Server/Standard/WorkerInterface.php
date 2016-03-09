<?php

namespace Seeker\Server\Standard;

interface WorkerInterface
{
	public function setPid($pid);
    public function getPid();
    public function setId($id);
    public function getId();
    public function setServer($server);
    public function getServer();
    public function onStart();
    public function onStop();
}