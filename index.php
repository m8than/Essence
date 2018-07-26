<?php
const APP_ROOT = __DIR__ . '/';
require_once './Essence/essence.inc.php';

use Essence\Application\EssenceApplication;

$app = new EssenceApplication(APP_ROOT . 'app');
$app->registerSingleton('Essence\Application\TestClass', ['Testing args1']);
$app->registerSingleton('Essence\Application\TestClass2', ['Testing args2']);
$app->registerSingleton('Essence\Application\TestClass3', ['Testing args3']);

//recursively auto resolves dependencies YAY
$testClassInstance = $app->get('TestClass');
?>