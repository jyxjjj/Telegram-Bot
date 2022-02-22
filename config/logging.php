<?php

$date = date('Y-m/d');
$path1 = storage_path("logs/$date.log");
$path2 = storage_path("logs/$date.sql.log");
$path3 = storage_path("logs/$date.emergency.log");
$dir = dirname($path1);
is_dir($dir) || mkdir($dir, 0775, true);

return [
    'default' => 'single',
    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
    'channels' => [
        'single' => [
            'driver' => 'single',
            'name' => 'single',
            'path' => $path1,
            'level' => 'debug',
            'bubble' => false,
            'permission' => 0644,
            'locking' => false,
        ],
        'emergency' => [
            'name' => 'emergency',
            'path' => $path3,
            'level' => 'debug',
            'permission' => 0644,
        ]
    ],
];
