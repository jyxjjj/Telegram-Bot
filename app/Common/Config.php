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

namespace App\Common;

class Config
{
    const array FILE_PERMISSIONS = [
        'file' => [
            'public' => 0644,
            'private' => 0644,
        ],
        'dir' => [
            'public' => 0775,
            'private' => 0775,
        ],
    ];

    const array CURL_HEADERS = [
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Sec-CH-DPR' => '1',
        'Sec-CH-Prefers-Color-Scheme' => 'dark',
        'Sec-CH-Prefers-Reduced-Motion' => 'no-preference',
        'Sec-CH-Prefers-Reduced-Transparency' => 'no-preference',
        'Sec-CH-UA' => '"Google Chrome";v="124", "Chromium";v="124","DESMG Web Client";v="2"',
        'Sec-CH-UA-Arch' => 'x86',
        'Sec-CH-UA-Bitness' => '64',
        'Sec-CH-UA-Full-Version-List' => '"Google Chrome";v="124.0.0.0", "Chromium";v="124.0.0.0","DESMG Web Client";v="2.3"',
        'Sec-CH-UA-Mobile' => '?0',
        'Sec-CH-UA-Model' => '',
        'Sec-CH-UA-Platform' => 'Fedora',
        'Sec-CH-UA-Platform-Version' => '40',
        'Sec-CH-UA-WoW64' => '?0',
        'Sec-CH-Viewport-Width' => '2560',
        'Sec-CH-Width' => '2560',
        'Sec-GPC' => '1',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'none',
        'Sec-Fetch-User' => '?1',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => "User_Agent_Protected_By_Client_Hints (https://web.dev/user-agent-client-hints/) Linux/6 Fedora/40 IA64 x86_64 Chrome/124.0.0.0 DESMG-Web-Client/2.3",
    ];

    const array PLAIN_HEADER = [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];

    const array CORS_HEADER = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'HEAD, GET, POST, OPTIONS',
        'Access-Control-Max-Age' => '3600',
    ];
}
