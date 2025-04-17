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

use InvalidArgumentException;

class LevelExp
{
    /**
     * @param int $level
     * @param string $exp
     * @param string $addonExp
     * @return void
     */
    public static function addExp(int &$level, string &$exp, string $addonExp): void
    {
        if ($level <= 0) {
            throw new InvalidArgumentException('Level must be greater than 0.');
        }
        if ($level >= 100000) {
            $level = 100000;
            $exp = '0';
            return;
        }
        $nextLevelExp = self::getExp($level + 1);
        $exp = bcadd($exp, $addonExp, 0);
        if (bccomp($exp, $nextLevelExp, 0) >= 0) {
            $exp = bcsub($exp, $nextLevelExp, 0);
            $level++;
        }
    }

    /**
     * @param int $level
     * @return string
     */
    public static function getExp(int $level): string
    {
        if ($level <= 0) {
            throw new InvalidArgumentException('Level must be greater than 0.');
        }
        return bcmul(bcpow($level, M_E, 0), 100, 0);
    }
}
