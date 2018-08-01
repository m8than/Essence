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
     * Stores a alias to class map (takes priority)
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Finds an entry by its identifier and returns it.
     *
     * @param string $id
     * @return mixed Entry
     */
    public function get($id)
    {
        if(!$this->has($id)) {
            throw new NotFoundException($id);
        }

        $className = $this->_resolveFullClassName($id);

        $entry = $this->entries[$className];
        if(!$entry->instanceExists()) {
            $arguments = $entry->getArgs();
            $this->_resolveMethodDependencies($className, '__construct', $arguments);
            $entry->setArgs($arguments);
        }
        return $entry->getInstance();
    }

    /**
     * If identifier exists, returns true. Otherwise returns false.
     *
     * @param string $id
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->entries[$id]) || isset($this->aliases[$id]) || isset($this->interfaceAliases[$id]) || isset($this->shortNameAliases[$id]);
    }

    /**
     * Runs specified method after
     *
     * @param object $class
     * @param string $method
     * @param array $arguments
     * @return void
     */
    public function runMethod($class, $method, $arguments)
    {
        if(!$this->has($id)) {
            throw new NotFoundException($id);
        }

        $className = $this->_resolveFullClassName($id);

        $entry = $this->entries[$className];
        if($entry->getType() == ContainerEntry::TYPE_SINGLETON && $entry->instanceExists()) {
            $arguments = $entry->getArgs();
            $this->_resolveMethodDependencies($className, $arguments);
            $entry->setArgs($arguments);
        }
        return $entry->getInstance();
    }

    /**
     * Registers alias (string|interface)
     *
     * @param string $className
     * @param string $alias
     * @return void
     */
    public function registerAlias($className, $alias)
    {
        $this->aliases[$alias] = $className;
    }

    /**
     * Registers class name in the container to be used to create a new object whenever needed
     *
     * @param string $className
     * @param array $params
     * @return ContainerEntry
     */
    public function registerSingleton($className, $params = [])
    {
        return $this->_registerClass($className, $params, ContainerEntry::TYPE_SINGLETON);
    }

    /**
     * Registers class name in the container to be used to create a new object whenever needed
     *
     * @param string $className
     * @param array $params
     * @return ContainerEntry
     */
    public function registerBinding($className, $params = [])
    {
        return $this->_registerClass($className, $params, ContainerEntry::TYPE_GENERAL);
    }

    /**
     * Registers class name in the container
     * 
     * @param string $className
     * @param array $params
     * @param int $type
     * @return ContainerEntry
     */
    private function _registerClass($className, $params, $type)
    {
        foreach(class_implements($className) as $interface) {
            $this->interfaceAliases[$interface] = $className;
        }
        $this->shortNameAliases[self::_getShortName($className)] = $className;
        $this->entries[$className] = ContainerEntry::create($className)->type($type)->setArgs($params);
        return $this->entries[$className];
    }

     private function _resolveMethodDependencies($className, $method, &$arguments)
    {
        $reflection = new \ReflectionClass($className);
        if (method_exists($className, $method)) {
            $method = $reflection->getMethod($method);
            $arg_pos = 0;
            foreach($method->getParameters() as $param) {
                $class = $param->getClass();
                if ($class && !(isset($arguments[$arg_pos]) && is_object($arguments[$arg_pos]))) {
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
        } else if (isset($this->aliases[$className])) {
            return $this->aliases[$className];
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