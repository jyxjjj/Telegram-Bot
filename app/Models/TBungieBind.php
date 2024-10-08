<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
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

use Illuminate\Support\Carbon;

/**
 * @property int $id 主键
 * @property int $user_id 用户ID
 * @property int $membership_id 棒鸡用户ID
 * @property string $access_token 授权Token
 * @property string $refresh_token 刷新Token
 * @property int $expires_in Token有效期
 * @property int $refresh_expires_in 刷新Token有效期
 * @property int $token_created_at Token创建时间
 * @property int $refresh_token_created_at 刷新Token创建时间
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property ?int $deleted_at 删除时间
 */
class TBungieBind extends BaseModel
{
    protected $table = 'bungie_bind';

    public function getUser(int $user_id): ?TBungieBind
    {
        return $this->newQuery()
            ->where('user_id', $user_id)
            ->first();
    }

    public function saveUser(int $user_id, int $membership_id, string $access_token, string $refresh_token, int $expires_in, int $refresh_expires_in): bool|TBungieBind
    {
        $data = $this->newQuery()
            ->where('user_id', $user_id)
            ->exists();
        if ($data) {
            return $this->newQuery()
                ->where('user_id', $user_id)
                ->update([
                    'membership_id' => $membership_id,
                    'access_token' => $access_token,
                    'refresh_token' => $refresh_token,
                    'expires_in' => $expires_in,
                    'refresh_expires_in' => $refresh_expires_in,
                    'token_created_at' => Carbon::createFromTimestamp(LARAVEL_START),
                    'refresh_token_created_at' => Carbon::createFromTimestamp(LARAVEL_START),
                ]);
        } else {
            return $this->newQuery()
                ->create([
                    'user_id' => $user_id,
                    'membership_id' => $membership_id,
                    'access_token' => $access_token,
                    'refresh_token' => $refresh_token,
                    'expires_in' => $expires_in,
                    'refresh_expires_in' => $refresh_expires_in,
                    'token_created_at' => Carbon::createFromTimestamp(LARAVEL_START),
                    'refresh_token_created_at' => Carbon::createFromTimestamp(LARAVEL_START),
                ]);
        }
    }
}
