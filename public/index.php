<?php

if (PHP_MAJOR_VERSION != 8 && PHP_MINOR_VERSION != 1) {
    echo "PHP Version Mismatch\n";
    exit(130);
}

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
if (file_exists(__DIR__ . '/../storage/framework/maintenance.php')) {
    require __DIR__ . '/../storage/framework/maintenance.php';
}
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = tap($kernel->handle(
    $request = Request::capture()
))->send();
$kernel->terminate($request, $response);
