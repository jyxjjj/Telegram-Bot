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

namespace App\Common;

final readonly class B23
{
    const int XOR_CODE = 23_442_827_791_579;
    const int MASK_CODE = 2_251_799_813_685_247;
    const int MAX_AID = 2_251_799_813_685_248;
    const string ALPHABET = "FcwAPNKTMug3GV5Lj7EJnHpWsx4tb8haYeviqBz6rkCy12mUSDQX9RdoZf";

    /**
     * @param string $av
     * @return string
     */
    public static function AV2BV(string $av): string
    {
        $aid = substr($av, 2);
        $bvid = str_split(str_repeat(0, 9));
        $i = 8;
        $tmp = (self::MAX_AID | $aid) ^ self::XOR_CODE;
        while ($tmp > 0) {
            $r = $tmp % 58;
            $bvid[$i] = self::ALPHABET[$r];
            $tmp = bcdiv($tmp, 58);
            $i -= 1;
        }
        [$bvid[0], $bvid[6]] = [$bvid[6], $bvid[0]];
        [$bvid[1], $bvid[4]] = [$bvid[4], $bvid[1]];
        return 'BV1' . implode('', $bvid);
    }

    /**
     * @param string $bv
     * @return string
     */
    public static function BV2AV(string $bv): string
    {
        $bvid = str_split(substr($bv, 3));
        [$bvid[0], $bvid[6]] = [$bvid[6], $bvid[0]];
        [$bvid[1], $bvid[4]] = [$bvid[4], $bvid[1]];
        $tmp = 0;
        foreach ($bvid as $i) {
            $tmp = $tmp * 58 + strpos(self::ALPHABET, $i);
        }
        return 'av' . (($tmp & self::MASK_CODE) ^ self::XOR_CODE);
    }
}
