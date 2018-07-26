<?php
namespace Essence\Application;

class Autoloader
{
    private static $base_dir;

    public static function loadModule($_class)
    {
        $file_path = self::$base_dir . str_replace('\\', '/', $_class) . '.php';
		if(file_exists($file_path))	{
			require_once $file_path;
		}
    }
    
    public static function register($base_dir)
    {
        self::$base_dir = $base_dir;
        spl_autoload_register('self::loadModule');
    }
}
?>