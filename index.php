<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use app\Models\User;
use app\Models\UserPermission;
use Essence\Router\Router;

$app = new EssenceApplication(APP_ROOT . 'app');

Router::add('GET|POST', 'yes/{str}/yes{str}', 'test', 'test');
Router::add('GET|POST', 'yes/{str}/no{str}', 'test', 'test');
Router::add('GET|POST', 'no/{str}/no{str}', 'test', 'test');
Router::add('GET|POST', 'yes/{str}/yes', 'test', 'test');
Router::add('GET|POST', 'yes/{str}/no', 'test', 'test');
Router::add('GET|POST', 'yes//yes{str}', 'test', 'test');

Router::prepare('yes/neeeeway/yesfg');
?>