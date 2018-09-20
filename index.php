<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use Essence\Database\Query\Query;

$app = new EssenceApplication(APP_ROOT . 'app');
?>