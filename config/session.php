<?php
return [
    'driver' => 'redis',
    'connection' => 'session',
    'store' => 'redis',
    'lifetime' => 120,
    'expire_on_close' => true,
    'secure' => true,
    'domain' => null,
    'path' => '/',
    'cookie' => 'TGSESSID',
    'http_only' => true,
    'same_site' => 'lax',
    'encrypt' => false,
    'lottery' => [2, 100],
];
