<?php

if (PHP_MAJOR_VERSION != 8 || PHP_MINOR_VERSION != 3) {
    echo "PHP Version Mismatch\n";
    exit(130);
}

define('LARAVEL_START', microtime(true));
const MAINTENANCE_FILE = __DIR__ . '/../storage/framework/maintenance.php';
if (file_exists(MAINTENANCE_FILE)) {
    require MAINTENANCE_FILE;
}
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = tap(
    $kernel->handle(
        $request = Request::capture()
    )
)->send();
$kernel->terminate($request, $response);
