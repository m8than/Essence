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
     * @param array $args
     * @return mixed Entry
     */
    public function get($id, $args=[])
    {
        if(!$this->has($id)) {
            throw new NotFoundException($id);
        }

        $className = $this->_resolveFullClassName($id);

        return $this->entries[$className];
    }

    /**
     * Gets container entry and returns an instance of the class stored
     *
     * @param string $id
     * @param array $args
     * @return mixed Class
     */
    public function construct($id, $args=[])
    {
        $className = $this->_resolveFullClassName($id);

        $entry = $this->get($id);
        
        if(count($args)) { 
            $entry->setArgs($args);
        }

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
     * Runs specified method with dependency injection
     *
     * @param mixed $class
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function runMethod($class, $method, $arguments)
    {
        $this->_resolveMethodDependencies($class, $method, $arguments);
        if (is_string($class)) {
            return $class::$method(...$arguments);
        } elseif (is_object($class)) {
            return $class->$method(...$arguments);
        }
    }

    /**
     * Returns new instance of class with dependency injection
     *
     * @param string $class
     * @param array $arguments
     * @return object
     */
    public function newClass($class, $arguments)
    {
        $this->_resolveMethodDependencies($class, '__construct', $arguments);
        return new $class(...$arguments);
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
        foreach (class_implements($className) as $interface) {
            $this->interfaceAliases[$interface] = $className;
        }
        $this->shortNameAliases[self::_getShortName($className)] = $className;
        $this->entries[$className] = ContainerEntry::create($className)->type($type)->setArgs($params);
        if (method_exists($className, '__staticconstruct')) {
            $className::__staticconstruct();
        }
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
                    array_splice($arguments, $arg_pos, 0, [$this->construct($class->name)]);
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