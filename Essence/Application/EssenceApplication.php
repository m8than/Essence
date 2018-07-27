<?php
namespace Essence\Application;

use Essence\Container\Container;
use Essence\Config\AppConfig;
use Essence\Config\EnvConfig;

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
    private $appConfig;

    /**
     * Stores EnvConfig instance
     *
     * @var EnvConfig
     */
    private $envConfig;

    public function __construct($appDirectory)
    {
        $this->appDirectory = $appDirectory;
        $this->registerConfig();
    }

    private function registerConfig()
    {
        $this->registerSingleton(AppConfig::class, [$this->appDirectory . '/Config/app.conf']);
        $this->registerSingleton(EnvConfig::class, [$this->appDirectory . '/Config/env.conf']);

        $this->appConfig = $this->get(AppConfig::class);
        $this->envConfig = $this->get(EnvConfig::class);
    }
}
?>