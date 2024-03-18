<?php

return [
    'default' => 'smtp',
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'encryption' => 'ssl',
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => 10,
            'auth_mode' => null,
        ],
    ],
    'from' => [
        'address' => env('MAIL_USERNAME'),
        'name' => env('MAIL_NAME'),
    ],
    'reply_to' => [
        'address' => env('MAIL_USERNAME'),
        'name' => env('MAIL_NAME'),
    ],
];
