<?php

namespace Essence\Controller;

use Essence\Template\Template;
use Essence\Router\Router;


class Controller
{
    protected function view($name, $data = [])
    {
        if (session('essence.controlvariables') !== null) {
            $data = array_merge($data, session('essence.controlvariables'));
            session('essence.controlvariables', null);
        }
        $template = new Template($name, $data);
        return $template->output();
    }

    protected function redirectURL($url, $variables = null)
    {
        if (!is_null($variables)) {
            session('essence.controlvariables', $variables);
        }

        Router::redirect($url);
        return;
    }

    protected function redirectRoute($controller, $method, $variables = null)
    {
        if (!is_null($variables)) {
            session('essence.controlvariables', $variables);
        }

        Router::redirect($controller, $method);
        return;
    }
}
?>