<?php
const APP_ROOT = __DIR__ . '/';
const IN_ESSENCE = true;
require_once './Essence/bootstrap.php';

use Essence\Application\EssenceApplication;
use app\Models\User;
use app\Models\UserPermission;

$app = new EssenceApplication(APP_ROOT . 'app');

$user = User::fetchWith(1, [
            UserPermission::class
        ]);

$user->save();
?>