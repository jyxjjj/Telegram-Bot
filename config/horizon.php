<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
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
            'balance' => 'auto',
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
