<?php
namespace Essence\Application;

use PDO;
use Essence\Config\AppConfig;
use Essence\Config\EnvConfig;
use Essence\Database\Database;
use Essence\Container\Container;
use Essence\Database\Query\Query;
use Essence\Database\PDO\EssencePDO;
use Essence\Database\ORM\Record;
use Essence\Config\ConfigReader;

class EssenceApplication extends Container
{
    /**
     * Application directory location
     *
     * @var string
     */
    private $appDirectory;

    /**
     * Essence config
     *
     * @var string
     */
    private $config;

    private static $_containerInstance;
    
    public function __construct($appDirectory)
    {
        $this->appDirectory = $appDirectory . '/';
        self::$_containerInstance = $this;
        $this->registerSelf();
        $this->loadEssenceConfig();
        $this->registerConfig();
        $this->registerContainer();
    }

    private function registerSelf()
    {
        $this->registerSingleton(self::class)->setInstance($this);
    }

    private function loadEssenceConfig()
    {
        $this->config = require($this->appDirectory . 'essence.php');
    }

    private function registerConfig()
    {
        $this->registerSingleton(EnvConfig::class, [$this->appDirectory . $this->config['env']]);
        $this->registerSingleton(AppConfig::class, [$this->appDirectory . $this->config['app']]);
    }

    private function registerContainer()
    {
        $container = require($this->appDirectory . $this->config['dependencies']);
        
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