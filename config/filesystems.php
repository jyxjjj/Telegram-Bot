<?php
return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'permissions' => [
                'file' => [
                    'public' => 0644,
                    'private' => 0644,
                ],
                'dir' => [
                    'public' => 0775,
                    'private' => 0775,
                ],
            ],
        ],
    ],
    'links' => [
    ],
];
