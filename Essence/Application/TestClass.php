<?php

namespace Essence\Application;

class TestClass
{
    public $test;
    public function __construct($ttt, TestClass2 $testclass2)
    {
        $this->test = $ttt;
        $testclass2->echoStuff();
    }
}
?>