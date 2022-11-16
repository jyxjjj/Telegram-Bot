<?php

use App\Common\Config;

return [
    'default' => 'public',
    'disks' => [
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'permissions' => Config::FILE_PERMISSIONS,
        ],
        'telegram' => [
            'driver' => 'local',
            'root' => storage_path('app/telegram'),
            'permissions' => Config::FILE_PERMISSIONS,
        ],
    ],
    'links' => [
    ],
];
