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

namespace App\Common;

use DESMG\RFC8942\RequestHeader;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RequestHelper
{
    /**
     * @param int $connectTimeout
     * @param int $timeout
     * @param int $retry
     * @param int $retryDelay
     * @param array $options
     * @return PendingRequest
     */
    public static function getInstance(int $connectTimeout = 5, int $timeout = 5, int $retry = 3, int $retryDelay = 1000, array $options = []): PendingRequest
    {
        return Http
            ::withHeaders(
                new RequestHeader(
                    '2.3',
                    6,
                    40,
                    130
                )->getCURLHeaders()
            )
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->withOptions($options)
            ->connectTimeout($connectTimeout)
            ->timeout($timeout)
            ->retry($retry, $retryDelay, throw: false);
    }
}
