<?php
use Essence\Config\AppConfig;
use Essence\Config\EnvConfig;
use Essence\Application\EssenceApplication;

if(!function_exists('app')) {
    function app($key)
    {
        $app = EssenceApplication::getInstance()->construct(AppConfig::class);
        return dig($key, $app);
    }
}

if(!function_exists('env')) {
    function env($key)
    {
        $env = EssenceApplication::getInstance()->construct(EnvConfig::class);
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
    function get($className, $args=[])
    {
        return EssenceApplication::getInstance()->construct($className, $args);
    }
}
?>