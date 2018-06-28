<?php

namespace Essence\Container;

use Essence\Contracts\Container\Container as IContainer;

class Container implements IContainer
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
    public function get($id)
    {
        if(!$this->has($id)) {
            throw new NotFoundException($id . ' not found');
        }
        return $this->entries[$id]->getInstance();
    }

    /**
     * If identifier exists, returns true. Otherwise returns false.
     *
     * @param string $id
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->entries[$id]);
    }

    /**
     * Registers singleton object in the container
     *
     * @param object $className
     * @param mixed $params, ...
     * @return void
     */
    public function registerSingleton($className, ...$params)
    {
        $this->entries[$className] = new ContainerEntry($className, ContainerEntry::TYPE_SINGLETON, $params);
    }

    /**
     * Registers class name in the container to be used to create a new object whenever needed
     *
     * @param string $className
     * @param mixed $params, ...
     * @return void
     */
    public function registerClass($className, ...$params)
    {
        $this->entries[$className] = new ContainerEntry($className, ContainerEntry::TYPE_MULTI, $params);
    }
}