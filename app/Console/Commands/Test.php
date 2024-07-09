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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    protected $signature = 'test';
    protected $description = 'Test';

    public function handle(): int
    {
        // 1
        $data = file_get_contents('database/city.csv');
        $data = trim($data);
        $data = explode("\n", $data);
        $data = array_map(fn($v) => explode('    ', $v), $data);
        for ($i = 0; $i < count($data); $i++) {
            if (!isset($data[$i][2])) {
                $data[$i][2] = 0;
            }
        }
        $data = array_map(fn($v) => array_map(fn($v) => trim($v), $v), $data);
        $sql = "CREATE TEMPORARY TABLE `tg__city` (`id` INT PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(64) NOT NULL, `adcode` VARCHAR(6) NOT NULL, `citycode` VARCHAR(6) NOT NULL);";
        DB::statement($sql);
        $ts = array_map(fn($v) => "('$v[0]', '$v[1]', '$v[2]')", $data);
        $sql = "INSERT INTO `tg__city` (`name`, `adcode`, `citycode`) VALUES ";
        $sql .= implode(',', $ts);
        $sql .= ";";
        DB::statement($sql);
        $sql = "SELECT `citycode`, `adcode`, `name` FROM `tg__city` ORDER BY `adcode`;";
        $data = DB::select($sql);
        DB::statement("DROP TEMPORARY TABLE `tg__city`;");
        file_put_contents('database/city.json', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return self::SUCCESS;
    }
}
