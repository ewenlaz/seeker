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

function shared($name, $callback = null)
{
    static $services = [];
    if (!$callback) {
    	if (!$services[$name][1]) {
    		$services[$name][1] = $services[$name][0]();
    	}
    	return $services[$name][1];
    } else {
    	$services[$name] = [$callback, null];
    }
}

spl_autoload_register(function($class) {
    require __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php'; 
});
