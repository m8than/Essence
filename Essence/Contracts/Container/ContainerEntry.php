<?php

namespace Essence\Contracts\Container;

interface ContainerEntry
{
    /**
     * Class constructor
     *
     * @param string $className
     * @param int $type
     * @param mixed $params, ...
     */
    public function __construct($className, $type, ...$params);

    /**
     * Returns singleton instance or identified/random instance
     *
     * @param string $instanceId
     * @return mixed
     */
    public function getInstance($instanceId = '');
}