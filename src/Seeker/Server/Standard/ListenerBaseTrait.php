<?php
namespace Seeker\Server\Standard;

trait ListenerBaseTrait
{
    protected $port = null;
    protected $host = '';
    protected $worker = null;
    protected $setting = [];
    public function getWorker()
    {
        return $this->worker;
    }

    public function getHost()
    {
        return $this->host;
    }
    public function getPort()
    {
        return $this->port;
    }

    public function getSetting()
    {
        return $this->setting;
    }

    public function setting($setting)
    {
        $this->setting = $setting;
    }
}