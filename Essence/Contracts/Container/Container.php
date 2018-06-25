<?php

namespace Essence\Contracts\Container;

interface Container
{
    /**
     * Stores all entries by identifier.
     *
     * @var array
     */
    protected $entries = [];

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
     * Registers singleton object in the container
     *
     * @param object $abstract
     * @return void
     */
    public function registerSingleton($object);

    /**
     * Registers class name in the container to be used to create a new object whenever needed
     *
     * @param string $className
     * @return void
     */
    public function registerClass($className);
}