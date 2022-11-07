<?php

use App\Common\Config;

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'permissions' => Config::FILE_PERMISSIONS,
        ],
    ],
    'links' => [
    ],
];
