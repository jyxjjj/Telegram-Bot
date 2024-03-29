<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
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

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Symfony\Component\HttpFoundation\Request;

class TrustProxies extends Middleware
{
    protected $proxies = [
        '10.0.0.0/8',
        '100.64.0.0/10',
        '127.0.0.0/8',
        '169.254.0.0/16',
        '172.16.0.0/12',
        '192.168.0.0/16',

        // https://www.cloudflare.com/ips-v4
        '103.21.244.0/22', // CloudFlare
        '103.22.200.0/22', // CloudFlare
        '103.31.4.0/22', // CloudFlare
        '104.16.0.0/13', // CloudFlare
        '104.24.0.0/14', // CloudFlare
        '108.162.192.0/18', // CloudFlare
        '131.0.72.0/22', // CloudFlare
        '141.101.64.0/18', // CloudFlare
        '162.158.0.0/15', // CloudFlare
        '172.64.0.0/13', // CloudFlare
        '173.245.48.0/20', // CloudFlare
        '188.114.96.0/20', // CloudFlare
        '190.93.240.0/20', // CloudFlare
        '197.234.240.0/22', // CloudFlare
        '198.41.128.0/17', // CloudFlare
        // https://www.cloudflare.com/ips-v6
        '2400:cb00::/32', // CloudFlare
        '2405:8100::/32', // CloudFlare
        '2405:b500::/32', // CloudFlare
        '2606:4700::/32', // CloudFlare
        '2803:f800::/32', // CloudFlare
        '2a06:98c0::/29', // CloudFlare
        '2c0f:f248::/32', // CloudFlare
    ];
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST;
}
