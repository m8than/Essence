<?php

namespace Essence\Contracts\Container;

interface ContainerEntry
{
    /**
     * Class constructor
     *
     * @param string $className
     */
    public function __construct($className);

    /**
     * returns new ContainerEntry instance
     * 
     * @param string $className
     * @return ContainerEntry
     */
    public static function create($className);

    /**
     * Returns instance
     *
     * @return mixed
     */
    public function getInstance();
    
    /**
     * Sets arguments that will be used to setup the instance
     *
     * @param mixed ...$params
     * @return ContainerEntry
     */
    public function setArgs($params);

    /**
     * Sets type of entry: singleton, bind
     *
     * @param int $type
     * @return ContainerEntry
     */
    public function type($type);
}