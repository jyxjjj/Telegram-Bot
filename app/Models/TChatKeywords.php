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

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id 主键
 * @property int $chat_id 聊天ID
 * @property string $keyword 关键字
 * @property TChatKeywordsTargetEnum $target 检测目标
 * @property TChatKeywordsOperationEnum $operation 执行操作
 * @property array $data 操作数据
 * @property int $enable 启用
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property ?int $deleted_at 删除时间
 */
class TChatKeywords extends BaseModel
{
    protected $table = 'chat_keywords';
    protected $casts = [
        'data' => 'array',
        'target' => TChatKeywordsTargetEnum::class,
        'operation' => TChatKeywordsOperationEnum::class,
    ];

    /**
     * @param int $chat_id
     * @return Collection<TChatKeywords>
     */
    public static function getKeywords(int $chat_id): mixed
    {
        $data = Cache::get("DB::TChatKeywords::chat_keywords::$chat_id");
        if ($data) {
            return $data;
        }
        $data = self::query()
            ->select('keyword', 'target', 'operation', 'data')
            ->where('chat_id', $chat_id)
            ->where('enable', 1)
            ->get();
        Cache::put("DB::TChatKeywords::chat_keywords::$chat_id", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
