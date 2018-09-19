<?php
namespace Essence\Application;

use PDO;
use Essence\Config\AppConfig;
use Essence\Config\EnvConfig;
use Essence\Database\Database;
use Essence\Container\Container;
use Essence\Database\Query\Query;
use Essence\Database\PDO\EssencePDO;

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
        $this->appDirectory = $appDirectory;
        self::$_containerInstance = $this;
        $this->registerSelf();
        $this->registerConfig();
        $this->registerDB();
    }

    private function registerSelf()
    {
        $this->registerSingleton(self::class)->setInstance($this);
    }

    private function registerConfig()
    {
        $this->registerSingleton(EnvConfig::class, [$this->appDirectory . '/Config/.env.php']);
        $this->registerSingleton(AppConfig::class, [$this->appDirectory . '/Config/.app.php']);
    }

    private function registerDB()
    {
        $dbConfig = config('database');
        
        $this->registerSingleton(EssencePDO::class,
        [
            "mysql:host={$dbConfig['hostname']};dbname={$dbConfig['database']}",
            $dbConfig['username'],
            $dbConfig['password']
        ]);
        $this->registerAlias(EssencePDO::class, PDO::class);

        $this->registerSingleton(Database::class);
        $this->registerBinding(Query::class);
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