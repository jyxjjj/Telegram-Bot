<?php

namespace App\Common;

final class IP
{
    public final static function getClientIpAndCountry(): string|null
    {
        $ip = self::getClientIp();
        $country = self::getClientIpCountry();
        return $country == null ? $ip : "$ip ($country)";
    }

    public final static function getClientIp(): string|null
    {
        return request()->server('HTTP_CF_CONNECTING_IP', request()->getClientIp());
    }

    public final static function getClientIpCountry(): string|null
    {
        return request()->server('HTTP_CF_IPCOUNTRY');
    }
}
