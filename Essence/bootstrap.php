<?php
DEFINE("ESSENCE_SECURE", true);
session_start();
require_once 'Application/GlobalFunctions.php';
require_once 'Application/Autoloader.php';
Essence\Application\Autoloader::register(APP_ROOT);
?>