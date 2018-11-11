<?php
namespace app\Controllers;

class Test extends \Essence\Controller\Controller
{
    public function Test($var1)
    {
        return $this->view('test.etpl', ['yes' => $var1]);
    }
}
?>