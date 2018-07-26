<?php

namespace Essence\Application;

class TestClass2
{
    public $test;
    public function __construct($ttt, TestClass3 $testclass)
    {
        $this->test = $ttt;
        $testclass->test();
    }
    public function echoShitLol()
    {
        echo $this->test;
    }
}
?>