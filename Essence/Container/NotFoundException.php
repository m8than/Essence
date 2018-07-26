<?php

namespace Essence\Container;

use Essence\Container\BaseException as ContainerException;

class NotFoundException extends ContainerException
{
    public function __construct($entryId)
    {
        parent::__construct($entryId . ' not found');
    }
}