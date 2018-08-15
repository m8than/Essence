<?php
namespace Essence\Application;

class Autoloader
{
    private static $base_dir;

    public static function loadModule($_class)
    {
        $class_path = self::$base_dir . str_replace('\\', '/', $_class);
        if (file_exists($class_path . '.php'))	{
            require_once $class_path . '.php';
        }
    }
    
    public static function register($base_dir)
    {
        self::$base_dir = $base_dir;
        spl_autoload_register('self::loadModule');
    }
}
?>