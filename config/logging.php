<?php

use App\Common\Log\DataBaseLogger;

$date = date('Y-m-d');

return [
    'default' => 'mysql',
    'deprecations' => 'deprecations',
    'channels' => [
        'mysql' => [
            'driver' => 'monolog',
            'name' => 'mysql',
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
        'sql' => [
            'driver' => 'single',
            'name' => 'sql',
            'path' => storage_path("logs/sql.log"),
            'days' => 3,
            'level' => 'debug',
            'permission' => 0644,
            'bubble' => false,
            'locking' => false,
        ],
        'emergency' => [
            'driver' => 'single',
            'name' => 'emergency',
            'path' => storage_path("logs/emergency.log"),
            'days' => 3,
            'level' => 'debug',
            'permission' => 0644,
            'bubble' => false,
            'locking' => false,
        ],
    ],
];
