<?php
const IN_ESSENCE = true;
DEFINE('APP_ROOT', realpath(__DIR__ . '/..') . '/');
require_once '../Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use app\Models\User;
use app\Models\UserPermission;
use Essence\Router\Router;

$app = new EssenceApplication(APP_ROOT . 'app');

Router::prepare($_GET['uri'] ?? '');
echo Router::dispatch();
?>