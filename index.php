<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use Essence\Application\Autoloader;
use Essence\Database\Query\Query;
use Essence\Database\Query\Raw;

$app = new EssenceApplication(APP_ROOT . 'app');

/*
$query = Query::create('Users U')
        ->leftJoin('UserPermissions UP', function($on) {
                $on->on('U.id', 'UP.user_id');
        })
        ->columns('U.username, UP.test as col')
        ->where(Raw::create('U.username'), 'yes');

$query = $query->selectRow();
*/

$query = Query::create('Users')
                ->increment('test')
                ->where('id', 1)
                ->update();
?>