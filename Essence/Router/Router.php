<?php

namespace Essence\Router;

use Essence\Application\EssenceApplication;


class Router
{
    private static $routes = [];
    private static $variables = [
        '{str}'
    ];
    private static $namespace = '';

    private static $route_match = '';
    private static $route_variables = '';

    public static function add($type, $uri, $controller, $method, $middleware = "")
    {
        $types = explode('|', $type);

        if (strpos($controller, '\\') === false) {
            $controller = self::$namespace . '\\' . $controller;
        }

        self::$routes[] = [
            'types' => $types,
            'uri' => $uri,
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware
        ];
    }

    public static function prepare($uri)
    {
        $key = md5(microtime() . rand(100000,99999));
        $variables_to_nothing = array_combine(self::$variables, array_fill(0, count(self::$variables), $key));
        foreach(self::$routes as $route) {
            //TODO: check request method

            //get parts of route around variables
            $route_parts = explode($key, strtr($route['uri'], $variables_to_nothing));

            //get variables in route
            $route_parts_to_nothing = array_combine(array_filter($route_parts), array_fill(0, count(array_filter($route_parts)), $key));
            $route_variables = explode($key, strtr($route['uri'], $route_parts_to_nothing));

            //remove route parts from uri and see if the variables left match
            $uri_variables = explode($key, strtr($uri, $route_parts_to_nothing));

            $uri_variables_to_nothing = array_combine(array_filter($uri_variables), array_fill(0, count(array_filter($uri_variables)), $key));
            $uri_parts = explode($key, strtr($uri, $uri_variables_to_nothing));

            //check that uri has every route part and that they are in order
            if ($route_parts == $uri_parts) {
                if (count($uri_variables) >= count($route_variables)) {
                    $found = true;
                    $variables = [];
                    for ($i = 0; $i < count($route_variables); $i++) {
                        if (!empty($route_variables[$i])) {
                            if (!self::filterVariable($route_variables[$i], $uri_variables[$i])){
                                $found = false;
                            } else {
                                $variables[] = $uri_variables[$i];
                            }
                        }
                    }
                    if ($found) {
                        self::$route_match = $route;
                        self::$route_variables = $variables;
                        print_r($route);
                        break;
                    }
                }
            }
        }
    }

    private static function filterVariable($type, $value)
    {
        switch($type)
        {
            case '{str}':
                return ctype_alnum($value);
        }
        return false;
    }

    public static function dispatch()
    {

    }

    public static function setNamespace($namespace)
    {
        self::$namespace = $namespace;
    }
}