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

final class IP
{
    public static function getClientIpInfos(): ?string
    {
        $request = request();
        $clientIp = $request->header('cf-connecting-ip', $request->getClientIp());
        $continent = $request->header('cf-ipcontinent');
        $country = $request->header('cf-ipcountry');
        $regionCode = $request->header('cf-region-code');
        $region = $request->header('cf-region');
        $city = $request->header('cf-ipcity');
        $latitude = $request->header('cf-iplatitude');
        $longitude = $request->header('cf-iplongitude');
        if ($city && $region && $regionCode && $country && $continent && $latitude && $longitude) {
            return sprintf('%s (%s, %s) (%s, %s, %s) [%s, %s]', $clientIp, $city, $region, $regionCode, $country, $continent, $latitude, $longitude);
        } elseif ($city && $region && $regionCode && $country && $continent) {
            return sprintf('%s (%s, %s) (%s, %s, %s)', $clientIp, $city, $region, $regionCode, $country, $continent);
        } elseif ($regionCode && $country && $continent) {
            return sprintf('%s (%s, %s, %s)', $clientIp, $regionCode, $country, $continent);
        } elseif ($country && $continent) {
            return sprintf('%s (%s, %s)', $clientIp, $country, $continent);
        } elseif ($country) {
            return sprintf('%s (%s)', $clientIp, $country);
        } else {
            return $clientIp;
        }
    }

    public static function getClientIp(): ?string
    {
        return request()->header('cf-connecting-ip', request()->getClientIp());
    }
}
