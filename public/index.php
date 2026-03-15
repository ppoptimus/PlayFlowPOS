<?php

define('LARAVEL_START', microtime(true));

// Temporary compatibility guard for running Laravel 6 on PHP 8.5.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
