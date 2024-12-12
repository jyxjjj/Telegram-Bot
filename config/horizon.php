<?php

return [
    'domain' => null,
    'path' => 'monitor/queue',
    'use' => 'queue',
    'prefix' => env('APP_NAME', 'app') . '_horizon:',
    'middleware' => [],
    'waits' => [
        'redis:default' => 60,
    ],
    'trim' => [
        'recent' => 360,
        'pending' => 360,
        'completed' => 360,
        'recent_failed' => 4320,
        'failed' => 4320,
        'monitored' => 4320,
    ],
    'silenced' => [
    ],
    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],
    'fast_termination' => false,
    'memory_limit' => 128,
    'defaults' => [
        'default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => true,
            'autoScalingStrategy' => 'size',
            'minProcesses' => 1,
            'maxProcesses' => 4,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 1,
            'maxTime' => 3600,
            'maxJobs' => 0,
            'memory' => 128,
            'tries' => 1,
            'timeout' => 1800,
            'sleep' => 0.05,
            'nice' => 0,
        ],
    ],
    'environments' => [
        '*' => [],
    ],
];
