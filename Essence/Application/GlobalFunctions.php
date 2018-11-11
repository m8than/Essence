<?php
use Essence\Config\AppConfig;
use Essence\Config\EssenceConfig;
use Essence\Application\EssenceApplication;


if (!function_exists('app')) {
    function app($key, $value = null)
    {
        $app = EssenceApplication::getInstance()->construct(AppConfig::class);
        return dig($key, $app, $value);
    }
}

if (!function_exists('env')) {
    function essence($key, $value = null)
    {
        $env = EssenceApplication::getInstance()->construct(EssenceConfig::class);
        return dig($key, $env, $value);
    }
}

if (!function_exists('dig')) {
    function dig($key, &$array, $value = null)
    {
        if ($value != null) {
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
    function session($key, $value = null)
    {
        return dig($key, $_SESSION, $value);
    }
}
?>