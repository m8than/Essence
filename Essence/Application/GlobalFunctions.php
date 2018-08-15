<?php
use Essence\Config\AppConfig;
use Essence\Config\EnvConfig;
use Essence\Application\EssenceApplication;

if(!function_exists('config')) {
    function config($key)
    {
        $config = EssenceApplication::getInstance()->get(AppConfig::class);
        return dig($key, $config);
    }
}

if(!function_exists('env')) {
    function env($key)
    {
        $env = EssenceApplication::getInstance()->get(EnvConfig::class);
        return dig($key, $env);
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

if(!function_exists('get')) {
    function get($className, $args)
    {
        return EssenceApplication::getInstance()->get($className, $args);
    }
}

?>