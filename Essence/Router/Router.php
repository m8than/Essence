<?php

namespace Essence\Router;

use Essence\Template\Template;


class Router
{
    private static $routes = [];
    private static $variables = [
        '{str}',
        '{int}'
    ];
    private static $controller_namespace = '';
    private static $middleware_namespace = '';

    private static $route_match = null;
    private static $route_variables = '';

    public static function add($type, $uri, $controller, $method, $middleware = "", $middleware_variables = [])
    {
        $types = explode('|', $type);

        if (strpos($controller, '\\') === false) {
            $controller = self::$controller_namespace . '\\' . $controller;
        }
        
        if (strpos($middleware, '\\') === false && $middleware != "") {
            $middleware = self::$middleware_namespace . '\\' . $middleware;
        }

        self::$routes[] = [
            'types' => $types,
            'uri' => $uri,
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware,
            'middleware_variables' => $middleware_variables
        ];
    }

    public static function prepare($uri)
    {
        Template::addGlobal(['url' => $uri]);
        $key = md5(microtime() . rand(100000,99999));
        $variables_to_nothing = array_combine(self::$variables, array_fill(0, count(self::$variables), $key));
        foreach(self::$routes as $route) {
            if (!in_array($_SERVER['REQUEST_METHOD'], $route['types'])) {
                continue;
            }

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
            case '{int}':
                return ctype_digit($value);
        }
        return false;
    }

    public static function dispatch()
    {
        if (self::$route_match != null) {
            $app = get(\Essence\Application\EssenceApplication::class);

            if (!empty(self::$route_match['middleware'])) {
                $middleware = $app->newClass(self::$route_match['middleware'], []);
                if (!$app->runMethod($middleware, "handle", self::$route_match['middleware_variables'])) {
                    return $app->runMethod($middleware, "exit", self::$route_match['middleware_variables']);
                }
            }

            $controller = $app->newClass(self::$route_match['controller'], []);
            return $app->runMethod($controller, self::$route_match['method'], self::$route_variables);
        }
    }

    public static function redirect($url_or_controller, $method = null, $variables = null)
    {
        if ($method !== null) {
            foreach(self::$routes as $route) {
                if (str_replace(self::$controller_namespace . '\\', '', $route['controller']) == $url_or_controller && $route['method'] == $method) {
                    $url = str_replace(self::$variables, $variables, $route['uri']);
<<<<<<< HEAD
                    header('LOCATION: '. app('website.url') . '/' . $url);
=======
                    header('LOCATION: '. essence('website_url') . '/' . $url);
>>>>>>> 50677528f3773513162fa0c873b7a0fdbcd4044c
                }
            }
        } else {
            header('LOCATION: '. $url_or_controller);
        }
    }

    public static function setControllerNamespace($namespace)
    {
        self::$controller_namespace = $namespace;
    }

    public static function setMiddlewareNamespace($namespace)
    {
        self::$middleware_namespace = $namespace;
    }
}