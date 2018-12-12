<?php

if(!function_exists('generateDataString')) {
    function generateDataString($data, $whitelist)
    {
        foreach($whitelist as $key) {
            echo "data-{$key}=\"{$data[$key]}\" ";
        }
    }
}