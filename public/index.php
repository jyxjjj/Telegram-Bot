<?php

use Illuminate\Http\Request;

if (PHP_MAJOR_VERSION != 8 || PHP_MINOR_VERSION != 3) {
    echo "PHP Version Mismatch\n";
    exit(130);
}

define('LARAVEL_START', (new DateTime())->format('U.u'));
const MAINTENANCE_FILE = __DIR__ . '/../storage/framework/maintenance.php';
if (file_exists(MAINTENANCE_FILE)) {
    require MAINTENANCE_FILE;
}
require __DIR__ . '/../vendor/autoload.php';

(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
