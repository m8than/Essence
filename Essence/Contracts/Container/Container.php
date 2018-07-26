<?php

namespace Essence\Contracts\Container;

interface Container
{
    /**
     * Finds an entry by its identifier and returns it.
     *
     * @param string $id
     * @return mixed Entry
     */
    public function get($id);

    /**
     * If identifier exists, returns true. Otherwise returns false.
     *
     * @param string $id
     * @return boolean
     */
    public function has($id);

    /**
     * Registers class to be used as a singleton
     *
     * @param string $className
     * @param array $params
     * @return ContainerEntry
     */
    public function registerSingleton($className, $params);
    
    /**
     * Registers class
     * 
     * @param string $className
     * @param array $params
     * @return ContainerEntry
     */
    public function registerBinding($className, $params);
}