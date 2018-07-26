<?php

namespace Essence\Application;

class TestClass3
{
    public $test;
    public function __construct($ttt)
    {
        $this->test = $ttt;
    }
    public function test()
    {
        echo 'recursive dependency injection ?!?!?!';
    }
}
?>