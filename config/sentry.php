<?php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),
    'environment' => env('SENTRY_ENVIRONMENT'),
    'breadcrumbs' => [
        'logs' => true,
        'sql_queries' => true,
        'sql_bindings' => true,
        'queue_info' => true,
        'command_info' => true,
    ],
    'tracing' => [
        'queue_job_transactions' => env('SENTRY_TRACE_QUEUE_ENABLED', false),
        'queue_jobs' => true,
        'sql_queries' => true,
        'sql_origin' => true,
        'views' => true,
        'default_integrations' => true,
    ],
    // @see: https://docs.sentry.io/platforms/php/configuration/options/#send-default-pii
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),
    'traces_sample_rate' => (float)(env('SENTRY_TRACES_SAMPLE_RATE', 0.0)),
    'controllers_base_namespace' => env('SENTRY_CONTROLLERS_BASE_NAMESPACE', 'App\\Http\\Controllers'),
];
