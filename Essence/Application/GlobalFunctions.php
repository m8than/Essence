<?php
function config($key)
{
    global $app;
    $config = $app->get('Essence\\Config\\AppConfig');
    $parts = explode('.', $key);
    $value = $config[array_shift($parts)];
    while(count($parts)) {
        $value = $value[array_shift($parts)];
    }
    return $value;
}
?>