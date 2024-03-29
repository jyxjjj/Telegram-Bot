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
 * @property int $chat_id 聊天ID
 * @property int $admin_id 管理员列表
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property ?int $deleted_at 删除时间
 */
class TChatAdmins extends BaseModel
{
    protected $table = 'chat_admins';

    /**
     * @param $chat_id
     * @param $admin_id
     * @return TChatAdmins
     */
    public static function addAdmin($chat_id, $admin_id): TChatAdmins
    {
        $data = self::query()
            ->create(
                [
                    'chat_id' => $chat_id,
                    'admin_id' => $admin_id,
                ]
            );
        Cache::forget("DB::TChatAdmins::chat_admins::$chat_id");
        return $data;
    }

    /**
     * @param $chat_id
     * @return void
     */
    public static function clearAdmin($chat_id): void
    {
        self::query()
            ->where('chat_id', $chat_id)
            ->delete();
        Cache::forget("DB::TChatAdmins::chat_admins::$chat_id");
    }

    /**
     * @param $chat_id
     * @return array
     */
    public static function getChatAdmins($chat_id): array
    {
        $data = Cache::get("DB::TChatAdmins::chat_admins::$chat_id");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->select('admin_id')
            ->where('chat_id', $chat_id)
            ->pluck('admin_id')
            ->toArray();
        $data = array_merge($data, [(int)env('TELEGRAM_ADMIN_USER_ID')]);
        Cache::put("DB::TChatAdmins::chat_admins::$chat_id", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
