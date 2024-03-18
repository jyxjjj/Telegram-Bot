<?php
return [
    'default' => 'redis',
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => 'queue',
            'retry_after' => 60,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],
    'failed' => [
        'driver' => 'database-uuids',
        'database' => 'mariadb',
        'table' => 'failed_jobs',
    ],
];
