<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto-detect core directory (cPanel shared hosting layout ../core vs standard layout ../)
$coreDir = file_exists(__DIR__ . '/../core/vendor/autoload.php')
    ? __DIR__ . '/../core'
    : __DIR__ . '/..';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $coreDir . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $coreDir . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once $coreDir . '/bootstrap/app.php')
    ->handleRequest(Request::capture());

