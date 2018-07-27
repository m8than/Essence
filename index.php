<?php
const APP_ROOT = __DIR__ . '/';
require_once './Essence/essence.inc.php';

use Essence\Application\EssenceApplication;

$app = new EssenceApplication(APP_ROOT . 'app');
?>