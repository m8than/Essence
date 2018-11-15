<?php
use Essence\Config\AppConfig;
use Essence\Config\EssenceConfig;
use Essence\Application\EssenceApplication;


if (!function_exists('app')) {
    function app($key, $value = '')
    {
        $app = EssenceApplication::getInstance()->construct(AppConfig::class);
        return dig($key, $app, $value);
    }
}

if (!function_exists('env')) {
    function essence($key, $value = '')
    {
        $env = EssenceApplication::getInstance()->construct(EssenceConfig::class);
        return dig($key, $env, $value);
    }
}

if (!function_exists('dig')) {
    function dig($key, &$array, $value = '')
    {
        if ($value !== '') {
            $temp = &$array;
            $parts = explode('.', $key);
            foreach($parts as $key) {
                $temp = &$temp[$key];
            }
            $temp = $value;
        } else {
            $temp = $array;
            $parts = explode('.', $key);
            foreach($parts as $key) {
                if (!isset($temp[$key])) { 
                    return null;
                }
                $temp = $temp[$key];
            }
        }

        return $temp;
    }
}

if (!function_exists('get')) {
    function get($className, $args=[])
    {
        return EssenceApplication::getInstance()->construct($className, $args);
    }
}

if (!function_exists('session')) {
    function session($key, $value = '')
    {
        return dig($key, $_SESSION, $value);
    }
}
?>