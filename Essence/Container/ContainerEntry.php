<?php

namespace Essence\Container;

use Essence\Contracts\Container\ContainerEntry as IContainerEntry;

class ContainerEntry implements IContainerEntry
{
    /**
     * Type constants
     */
    public const TYPE_SINGLETON = 1;
    public const TYPE_MULTI = 2;

    /**
     * Array of parameters to use if references unavailable
     *
     * @var array
     */
    private $params;

    /**
     * The full name of the class including namespace
     *
     * @var string
     */
    private $className;

    /**
     * Type of ServiceProvider, eg
     *
     * @var int
     */
    private $type;

    /**
     * Stores initialised objects where the key is the class they were initialised for.
     *
     * @var array
     */
    private $initialisedObjects = [];

    /**
     * Class constructor
     *
     * @param string $className
     * @param int $type
     */
    public function __construct($className, $type, ...$params)
    {
        $this->className = $className;
        $this->type = $type;
        $this->params = $params;
    }

    /**
     * Returns singleton instance or identified/random instance
     *
     * @param string $instanceId
     * @return mixed
     */
    public function getInstance($instanceId = '')
    {
        
    }
}