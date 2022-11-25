<?php

namespace App\Common;

final class IP
{
    public final static function getClientIpInfos(): ?string
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

    public final static function getClientIp(): ?string
    {
        return request()->header('cf-connecting-ip', request()->getClientIp());
    }

    public final static function getClientIpCountry(): ?string
    {
        return request()->header('cf-ipcountry');
    }

    public final static function getClientIpContinent(): ?string
    {
        return request()->header('cf-ipcontinent');
    }

    public final static function getClientIpCity(): ?string
    {
        return request()->header('cf-ipcity');
    }

    public final static function getClientIpLatitude(): ?string
    {
        return request()->header('cf-iplatitude');
    }

    public final static function getClientIpLongitude(): ?string
    {
        return request()->header('cf-iplongitude');
    }
}
