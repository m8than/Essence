<?php

use Essence\Config\AppConfig;

if(!function_exists('config')) {
    function config($key)
    {
        global $app;
        $config = $app->get(AppConfig::class);
        return dig($key, $config);
    }
}

if(!function_exists('dig')) {
    function dig($key, $array)
    {
        $parts = explode('.', $key);
        $value = $array[array_shift($parts)];
        while(count($parts)) {
            $value = $value[array_shift($parts)];
        }
        return $value;
    }
}
?>