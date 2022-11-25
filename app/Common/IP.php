<?php

namespace App\Common;

final class IP
{
    public static function getClientIpInfos(): ?string
    {
        $ip = self::getClientIp();
        $country = self::getClientIpCountry();
        $continent = self::getClientIpContinent();
        $city = self::getClientIpCity();
        $lat = self::getClientIpLatitude();
        $lon = self::getClientIpLongitude();
        return
            !$country ?
                $ip :
                (
                !$continent ?
                    "$ip ($country)" :
                    (
                    !$city ?
                        "$ip ($continent, $country)" :
                        (
                        !$lat || !$lon ?
                            "$ip ($city, $continent, $country)" :
                            "$ip ($city, $continent, $country) [$lat, $lon]"
                        )
                    )
                );
    }

    public static function getClientIp(): ?string
    {
        return request()->header('cf-connecting-ip', request()->getClientIp());
    }

    public static function getClientIpCountry(): ?string
    {
        return request()->header('cf-ipcountry');
    }

    public static function getClientIpContinent(): ?string
    {
        return request()->header('cf-ipcontinent');
    }

    public static function getClientIpCity(): ?string
    {
        return request()->header('cf-ipcity');
    }

    public static function getClientIpLatitude(): ?string
    {
        return request()->header('cf-iplatitude');
    }

    public static function getClientIpLongitude(): ?string
    {
        return request()->header('cf-iplongitude');
    }
}
