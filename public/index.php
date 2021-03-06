<?php
const IN_ESSENCE = true;
DEFINE('APP_ROOT', realpath(__DIR__ . '/..') . '/');
require_once '../Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use Essence\Router\Router;

$app = new EssenceApplication(APP_ROOT . 'app');

date_default_timezone_set(app('timezone'));

Router::prepare($_GET['uri'] ?? '');
echo Router::dispatch();
?>