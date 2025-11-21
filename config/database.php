<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
return [
    'default' => 'mariadb',
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => false,
    ],
    'connections' => [
        'mariadb' => [
            'driver' => 'mariadb',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', ''),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD'),
            'unix_socket' => PHP_OS === 'Linux' ? env('DB_SOCKET') : '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix' => 'tg__',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => extension_loaded('pdo_mysql') ? array_filter([PDO\Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),]) : [],
        ],
    ],
    'redis' => [
        'client' => 'phpredis',
        'options' => [
            'cluster' => 'redis',
            'prefix' => env('APP_NAME', 'app') . '_',
        ],
        'default' => [
            'host' => PHP_OS === 'Linux' ? env('REDIS_SOCKET') : env('REDIS_HOST', '127.0.0.1'),
            'port' => PHP_OS === 'Linux' ? 0 : env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => 0,
        ],
        'cache' => [
            'host' => PHP_OS === 'Linux' ? env('REDIS_SOCKET') : env('REDIS_HOST', '127.0.0.1'),
            'port' => PHP_OS === 'Linux' ? 0 : env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => 1,
        ],
        'session' => [
            'host' => PHP_OS === 'Linux' ? env('REDIS_SOCKET') : env('REDIS_HOST', '127.0.0.1'),
            'port' => PHP_OS === 'Linux' ? 0 : env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => 2,
        ],
        'queue' => [
            'host' => PHP_OS === 'Linux' ? env('REDIS_SOCKET') : env('REDIS_HOST', '127.0.0.1'),
            'port' => PHP_OS === 'Linux' ? 0 : env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => 3,
        ],
    ],
];
