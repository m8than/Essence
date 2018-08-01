<?php
namespace Essence\Config;

use IteratorAggregate;
use ArrayAccess;
use Countable;

class AppConfig extends ConfigReader implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * Stores link to envconfig
     *
     * @var EnvConfig
     */
    protected $env;

    public function __construct($location, EnvConfig $env)
    {
        $this->env = $env;
        parent::__construct($location);
    }
}
?>