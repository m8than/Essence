<?php
namespace Essence\Config;

class AppConfig extends ConfigReader
{
    /**
     * Stores link to globalconfig
     *
     * @var GlobalConfig
     */
    protected $globalConfig;

    public function __construct($location, GlobalConfig $globalConfig)
    {
        $this->globalConfig = $globalConfig;
        parent::__construct($location);
    }
}
?>