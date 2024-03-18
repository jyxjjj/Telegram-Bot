<?php

use App\Common\Log\DataBaseLogger;

$date = date('Y-m-d');

return [
    'default' => 'mariadb',
    'deprecations' => 'deprecations',
    'channels' => [
        'mariadb' => [
            'driver' => 'monolog',
            'name' => 'mariadb',
            'handler' => DataBaseLogger::class,
            'level' => 'debug',
            'bubble' => false,
            'locking' => false,
        ],
        'deprecations' => [
            'driver' => 'monolog',
            'name' => 'deprecations',
            'handler' => DataBaseLogger::class,
            'level' => 'debug',
            'bubble' => false,
            'locking' => false,
        ],
        'perf' => [
            'driver' => 'monolog',
            'name' => 'perf',
            'handler' => DataBaseLogger::class,
            'level' => 'debug',
            'bubble' => false,
            'locking' => false,
        ],
        'sql' => [
            'driver' => 'single',
            'name' => 'sql',
            'path' => storage_path("logs/$date.sql.log"),
            'level' => 'debug',
            'permission' => 0644,
            'bubble' => false,
            'locking' => false,
        ],
        'emergency' => [
            'driver' => 'single',
            'name' => 'emergency',
            'path' => storage_path("logs/$date.emergency.log"),
            'level' => 'debug',
            'permission' => 0644,
            'bubble' => false,
            'locking' => false,
        ],
    ],
];
