<?php

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
     * 获取上次获取的版本号
     * @param Software $software
     * @return string
     */
    public static function getLastVersion(Software $software): string
    {
        return Cache::get("Schedule::UpdateSubscribe::last_version::$software->value", '');
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
}
