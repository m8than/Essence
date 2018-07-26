<?php

namespace Essence\Container;

use Essence\Contracts\Container\ContainerEntry as IContainerEntry;

class ContainerEntry implements IContainerEntry
{
    public const TYPE_GENERAL = 0;
    public const TYPE_SINGLETON = 1;

    /**
     * Array of parameters to use if references unavailable
     *
     * @var array
     */
    private $arguments;

    /**
     * The full name of the class including namespace
     *
     * @var string
     */
    private $className;

    /**
     * Stores instance of the class if singleton
     *
     * @var mixed
     */
    private $instance;

    /**
     * Stores entry type
     * 
     * @var int
     */
    private $type;

    /**
     * Class constructor
     *
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * returns new ContainerEntry instance
     * 
     * @param string $className
     * @return ContainerEntry
     */
    public function create($className)
    {
        return new static($className);
    }

    /**
     * Returns instance
     *
     * @return mixed
     */
    public function getInstance()
    {
        switch($this->type) {
            case self::TYPE_GENERAL:
                return new $this->className(...$this->arguments);
                break;
            case self::TYPE_SINGLETON:
                if($this->instance == null) {
                    $this->instance = new $this->className(...$this->arguments);
                }
                return $this->instance;
                break;
            default:
                throw new BaseException('Invalid ContainerEntry type');
        }
    }
    
    /**
     * Sets arguments
     *
     * @param mixed $params
     * @return ContainerEntry
     */
    public function setArgs($params)
    {
        $this->arguments = $params;
        return $this;
    }

    /**
     * Returns args
     *
     * @param mixed $params
     * @return array
     */
    public function getArgs()
    {
        return $this->arguments;
    }

    /**
     * Sets type of entry: singleton, bind
     *
     * @param int $type
     * @return ContainerEntry
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }
}