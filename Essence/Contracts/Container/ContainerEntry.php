<?php

namespace Essence\Contracts\Container;

interface ContainerEntry
{
    /**
     * Type constants
     */
    public const TYPE_SINGLETON = 1;
    public const TYPE_MULTI = 2;

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
    public function __construct($className, $type);
}