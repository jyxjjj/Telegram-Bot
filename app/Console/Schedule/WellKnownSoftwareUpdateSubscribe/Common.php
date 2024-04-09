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

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Common
{
    /**
     * @var array
     */
    public static array $instances = [];

    /**
     * @var string
     */
    private static string $emoji = '';

    /**
     * 缓存HTTP响应中 <b>Last-Modified</b> 值
     * @param Software $software
     * @param string $lastModified
     * @return bool
     */
    public static function cacheLastModified(Software $software, string $lastModified): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_modified::$software->value", $lastModified, Carbon::now()->addMonths(3));
    }

    /**
     * \ud83c\udf89
     * @return string
     */
    public static function emoji(): string
    {
        if (self::$emoji == '') {
            self::$emoji = str_repeat(json_decode('["\ud83c\udf89"]', true)[0], 3);
        }
        return self::$emoji;
    }

    /**
     * 获取已缓存的HTTP响应中 <b>Last-Modified</b> 值
     * @param Software $software
     * @return string
     */
    public static function getLastModified(Software $software): string
    {
        return Cache::get("Schedule::UpdateSubscribe::last_modified::$software->value", '');
    }

    /**
     * 获取上次发送到聊天的版本号
     * @param Software $software
     * @param int $chat_id
     * @return string
     */
    public static function getLastSend(Software $software, int $chat_id): string
    {
        return Cache::get("Schedule::UpdateSubscribe::last_send::$chat_id::$software->value", '');
    }

    /**
     * 获取上次获取的版本号
     * @param Software $software
     * @return string
     */
    public static function getLastVersion(Software $software): string
    {
        return Cache::get("Schedule::UpdateSubscribe::last_version::$software->value", '');
    }

    /**
     * 设置上次发送到聊天的版本号
     * @param Software $software
     * @param int $chat_id
     * @param string $version
     * @return bool
     */
    public static function setLastSend(Software $software, int $chat_id, string $version): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_send::$chat_id::$software->value", $version, Carbon::now()->addMonths(3));
    }

    /**
     * 设置上次获取的版本号
     * @param Software $software
     * @param string $version
     * @return bool
     */
    public static function setLastVersion(Software $software, string $version): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_version::$software->value", $version, Carbon::now()->addMonths(3));
    }
}
