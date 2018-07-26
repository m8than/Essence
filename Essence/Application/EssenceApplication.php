<?php
namespace Essence\Application;

use Essence\Container\Container;
use Essence\Container\ContainerEntry;

class EssenceApplication extends Container
{
    /**
     * Application directory location
     *
     * @var string
     */
    private $appDirectory;

    public function __construct($appDirectory)
    {
        $this->appDirectory = $appDirectory;
    }
}
?>