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

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id 主键
 * @property int $chat_id 聊天ID
 * @property int $user_id 用户ID
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property ?int $deleted_at 删除时间
 */
class TChatKeywordsWhiteLists extends BaseModel
{
    protected $table = 'chat_keywords_white_lists';

    /**
     * @param $chat_id
     * @return array
     */
    public static function getChatWhiteLists($chat_id): array
    {
        $data = Cache::get("DB::TChatKeywordsWhiteLists::chat_keywords_white_lists::$chat_id");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->select('user_id')
            ->where('chat_id', $chat_id)
            ->pluck('user_id')
            ->toArray();
        Cache::put("DB::TChatKeywordsWhiteLists::chat_keywords_white_lists::$chat_id", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
