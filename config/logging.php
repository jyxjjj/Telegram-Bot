<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Addon License: https://www.desmg.com/policies/license
 *
 * Released under GNU General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use App\Common\Log\DataBaseLogger;

return [
    'default' => 'mariadb',
    'deprecations' => 'deprecations',
    'channels' => [
        'mariadb' => [
            'driver' => 'monolog',
            'name' => 'mariadb',
            'handler' => DataBaseLogger::class,
            'level' => 'debug',
            'bubble' => false,
            'locking' => false,
        ],
        'deprecations' => [
            'driver' => 'monolog',
            'name' => 'deprecations',
            'handler' => DataBaseLogger::class,
            'level' => 'debug',
            'bubble' => false,
            'locking' => false,
        ],
        'sql' => [
            'driver' => 'single',
            'name' => 'sql',
            'path' => storage_path("logs/sql.log"),
            'days' => 3,
            'level' => 'debug',
            'permission' => 0644,
            'bubble' => false,
            'locking' => false,
        ],
        'emergency' => [
            'driver' => 'single',
            'name' => 'emergency',
            'path' => storage_path("logs/emergency.log"),
            'days' => 3,
            'level' => 'debug',
            'permission' => 0644,
            'bubble' => false,
            'locking' => false,
        ],
    ],
];
