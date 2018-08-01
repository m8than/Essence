<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/essence.inc.php';

use Essence\Application\EssenceApplication;
use Essence\Application\Autoloader;

$app = new EssenceApplication(APP_ROOT . 'app');
?>