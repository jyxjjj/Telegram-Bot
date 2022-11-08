<?php

use App\Common\Config;

return [
    'default' => 'app',
    'disks' => [
        'app' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'permissions' => Config::FILE_PERMISSIONS,
        ],
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
