<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use app\Models\User;
use app\Models\UserPermission;
use Essence\Router\Router;

$app = new EssenceApplication(APP_ROOT . 'app');

Router::add('GET|POST', '{str}', 'test', 'test');

Router::prepare($_GET['uri'] ?? '');
echo Router::dispatch();
?>