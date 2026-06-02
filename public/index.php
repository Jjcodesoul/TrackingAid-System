<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

<<<<<<< HEAD
=======
// Determine if the application is in maintenance mode...
>>>>>>> db5ef8e73ac4431ebbfc800ae78adb114a103e05
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

<<<<<<< HEAD
require __DIR__.'/../vendor/autoload.php';


=======
// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
>>>>>>> db5ef8e73ac4431ebbfc800ae78adb114a103e05
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
