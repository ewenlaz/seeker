<?php
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
    if (strpos($class, 'Seeker\\') === 0) {
        $classFile = __DIR__ . '/' . trim(str_replace('\\', '/', $class), '/') . '.php';
        if (file_exists($classFile)) {
            require_once $classFile;
        }
    }
});
