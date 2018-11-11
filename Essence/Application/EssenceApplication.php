<?php
namespace Essence\Application;

use PDO;
use Essence\Config\AppConfig;
use Essence\Config\EssenceConfig;
use Essence\Database\Database;
use Essence\Container\Container;
use Essence\Database\Query\Query;
use Essence\Database\PDO\EssencePDO;
use Essence\Database\ORM\Record;
use Essence\Config\ConfigReader;
use Essence\Router\Router;

class EssenceApplication extends Container
{
    /**
     * Application directory location
     *
     * @var string
     */
    private $appDirectory;

    private static $_containerInstance;
    
    public function __construct($appDirectory)
    {
        $this->appDirectory = $appDirectory . '/';
        self::$_containerInstance = $this;
        $this->registerSelf();
        $this->loadEssenceConfig();
        $this->registerConfig();
        $this->registerContainer();
        $this->registerRoutes();
        get(EssenceConfig::class)->app_dir = $this->appDirectory;
    }

    private function registerRoutes()
    {
        Router::setNamespace(essence('controller_namespace'));
        
        if (essence('routes') != null) {
            include_once($this->appDirectory . essence('routes'));
        }
    }

    private function registerSelf()
    {
        $this->registerSingleton(self::class)->setInstance($this);
    }

    private function loadEssenceConfig()
    {
        $this->registerSingleton(EssenceConfig::class, [$this->appDirectory . 'essence.php']);
    }

    private function registerConfig()
    {
        $this->registerSingleton(AppConfig::class, [$this->appDirectory . essence('app')]);
    }

    private function registerContainer()
    {
        $container = require($this->appDirectory . essence('dependencies'));
        
        foreach($container['singletons'] as $class => $args) {
            $this->registerSingleton($class, $args);
        }

        foreach($container['bindings'] as $class => $args) {
            $this->registerBinding($class, $args);
        }

        foreach($container['aliases'] as $alias => $class) {
            $this->registerAlias($class, $alias);
        }
    }

    /**
     * Returns singleton of self
     *
     * @return self
     */
    public static function getInstance()
    {
        return self::$_containerInstance;
    }
}
?>