<?php

namespace Essence\Router;

class Request
{
    public $get = [];
    public $post = [];
<<<<<<< HEAD
    public $data = [];
=======
>>>>>>> 50677528f3773513162fa0c873b7a0fdbcd4044c

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