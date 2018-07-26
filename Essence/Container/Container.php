<?php

namespace Essence\Container;

use Essence\Contracts\Container\Container as IContainer;

class Container implements IContainer
{
    /**
     * Stores all entries by identifier.
     *
     * @var ContainerEntry[]
     */
    protected $entries = [];
    
    /**
     * Stores an interface to class map
     *
     * @var array
     */
    protected $interfaceAliases = [];

    /**
     * Stores a shortName to class map
     *
     * @var array
     */
    protected $shortNameAliases = [];

        /**
     * Finds an entry by its identifier and returns it.
     *
     * @param string $id
     * @return mixed Entry
     */
    public function get($id)
    {
        if(!$this->has($id)) {
            var_dump($this);
            throw new NotFoundException($id);
        }

        $className = $this->_resolveFullClassName($id);

        $entry = $this->entries[$className];
        $arguments = $entry->getArgs();
        $this->_resolveDependencies($className, $arguments);
        return $entry->setArgs($arguments)->getInstance();
    }

    /**
     * If identifier exists, returns true. Otherwise returns false.
     *
     * @param string $id
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->entries[$id]) || isset($this->interfaceAliases[$id]) || isset($this->shortNameAliases[$id]);
    }

    /**
     * Registers class name in the container to be used to create a new object whenever needed
     *
     * @param string $className
     * @return ContainerEntry
     */
    public function registerSingleton($className, $params)
    {
        foreach(class_implements($className) as $interface) {
            $this->interfaceAliases[$interface] = $className;
        }
        
        $this->shortNameAliases[self::_getShortName($className)] = $className;

        $this->entries[$className] = ContainerEntry::create($className)->type(ContainerEntry::TYPE_SINGLETON)->setArgs($params);
        return $this->entries[$className];
    }

    /**
     * Registers class name in the container to be used to create a new object whenever needed
     *
     * @param string $className
     * @return ContainerEntry
     */
    public function registerBinding($className, $params)
    {
        foreach(class_implements($className) as $interface) {
            $this->interfaceAliases[$interface] = $className;
        }

        $this->shortNameAliases[self::_getShortName($className)] = $className;

        $this->entries[$className] = ContainerEntry::create($className)->type(ContainerEntry::TYPE_GENERAL)->setArgs($params);
        return $this->entries[$className];
    }

    private function _resolveDependencies($className, &$arguments)
    {
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            $arg_pos = 0;
            foreach($constructor->getParameters() as $param) {
                $class = $param->getClass();
                if ($class) {
                    array_splice($arguments, $arg_pos, 0, [$this->get($class->name)]);
                }
                $arg_pos++;
            }
        }
    }

    private function _resolveFullClassName($className)
    {
        if (isset($this->entries[$className])) {
            return $className;
        } else if (isset($this->interfaceAliases[$className])) {
            return $this->interfaceAliases[$className];
        } else if (isset($this->shortNameAliases[$className])) {
            return $this->shortNameAliases[$className];
        } else {
            throw new NotFoundException($className . ' not found');
        }
    }

    private static function _getShortName($fullClassName)
    {
        return substr(strrchr($fullClassName, '\\'), 1);
    }
}