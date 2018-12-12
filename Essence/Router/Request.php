<?php

namespace Essence\Router;

class Request
{
    public $get = [];
    public $post = [];
    public $data = [];

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
    }
    
    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
}