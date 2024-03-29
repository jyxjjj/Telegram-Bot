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

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id 主键
 * @property int $user_id 用户ID
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property ?int $deleted_at 删除时间
 */
class TStarted extends BaseModel
{
    protected $table = 'started';

    /**
     * @param $user_id
     * @return TStarted
     */
    public static function addUser($user_id): TStarted
    {
        $data = Cache::get("DB::TStarted::user::$user_id");
        if ($data) {
            return $data;
        }
        $data = self::query()
            ->select('user_id')
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            $data = self::query()
                ->create([
                    'user_id' => $user_id,
                ]);
            Cache::put("DB::TStarted::user::$user_id", $data, Carbon::now()->addMinutes(5));
        }
        return $data;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public static function getUser($user_id): bool
    {
        $data = Cache::get("DB::TStarted::user::$user_id");
        if ($data) {
            return true;
        }
        $data = self::query()
            ->where('user_id', $user_id)
            ->first();
        if ($data) {
            Cache::put("DB::TStarted::user::$user_id", $data, Carbon::now()->addMinutes(5));
            return true;
        }
        return false;
    }
}
