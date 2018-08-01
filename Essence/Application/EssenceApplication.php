<?php
namespace Essence\Application;

use PDO;
use Essence\Container\Container;
use Essence\Config\AppConfig;
use Essence\Config\EnvConfig;
use Essence\Database\PDO\EssencePDO;

class EssenceApplication extends Container
{
    /**
     * Application directory location
     *
     * @var string
     */
    private $appDirectory;

    /**
     * Stores AppConfig instance
     *
     * @var AppConfig
     */
    public $appConfig;

    /**
     * Stores EnvConfig instance
     *
     * @var EnvConfig
     */
    public $envConfig;

    public function __construct($appDirectory)
    {
        $this->appDirectory = $appDirectory;
        $this->registerConfig();
    }

    private function registerConfig()
    {
        $this->registerSingleton(EnvConfig::class, [$this->appDirectory . '/Config/.global.php']);
        $this->registerSingleton(AppConfig::class, [$this->appDirectory . '/Config/.app.php']);

        $this->envConfig = $this->get(EnvConfig::class);
        $this->appConfig = $this->get(AppConfig::class);

        $dbConfig = config('database');
        $db = new PDO("mysql:host={$dbConfig['hostname']};dbname={$dbConfig['database']}", $dbConfig['username'], $dbConfig['password']);
        
        $this->registerSingleton(EssencePDO::class, [$this->appDirectory . '/Config/.app.php'])->setInstance($db);
        $this->registerAlias(EssencePDO::class, PDO::class);
    }
}
?>