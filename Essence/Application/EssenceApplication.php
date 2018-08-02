<?php
namespace Essence\Application;

use PDO;
use Essence\Container\Container;
use Essence\Config\AppConfig;
use Essence\Config\GlobalConfig;
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
     * @var GlobalConfig
     */
    public $globalConfig;

    public function __construct($appDirectory)
    {
        $this->appDirectory = $appDirectory;
        $this->registerConfig();
        $this->loadGlobalHelpers();
        $this->registerDB();
    }

    private function registerConfig()
    {
        $this->registerSingleton(GlobalConfig::class, [$this->appDirectory . '/Config/.global.php']);
        $this->registerSingleton(AppConfig::class, [$this->appDirectory . '/Config/.app.php']);

        $this->globalConfig = $this->get(GlobalConfig::class);
        $this->appConfig = $this->get(AppConfig::class);
    }

    private function loadGlobalHelpers()
    {
        require_once 'GlobalFunctions.php';
    }

    private function registerDB()
    {
        $dbConfig = $this->appConfig['database'];
        $this->registerSingleton(EssencePDO::class,
        ["mysql:host={$dbConfig['hostname']};dbname={$dbConfig['database']}", $dbConfig['username'], $dbConfig['password']]);
        $this->registerAlias(EssencePDO::class, PDO::class);
    }
}
?>