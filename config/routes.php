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

use App\Http\Controllers\BungieController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

function defineRoutes(): void
{
    Route::group(
        [
        ],
        function () {
            Route::get('/', [IndexController::class, 'index']);
            Route::get('/generate_204', fn() => response(null, Response::HTTP_NO_CONTENT)); // Captive Portal Detection
        }
    );

    Route::group(
        [
            'prefix' => 'api',
        ],
        function () {
            Route::post('/webhook', [IndexController::class, 'webhook']);
            Route::post('/sendMessage', [IndexController::class, 'sendMessage']);

            Route::group(
                [
                    'prefix' => 'bungie',
                ],
                function () {
                    Route::group(
                        [
                            'prefix' => 'oauth',
                        ],
                        function () {
                            Route::get('/login', [BungieController::class, 'login']);
                            Route::get('/redirect', [BungieController::class, 'redirect']);
                        }
                    );
                }
            );
        }
    );

}
