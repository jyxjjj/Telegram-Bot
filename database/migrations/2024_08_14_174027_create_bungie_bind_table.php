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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bungie_bind', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->comment('主键');
            $table->BigInteger('user_id')->default(0)->comment('用户ID');
            $table->BigInteger('membership_id')->default(0)->comment('棒鸡用户ID');
            $table->string('access_token', 256)->default('')->comment('授权Token');
            $table->string('refresh_token', 256)->default('')->comment('刷新Token');
            $table->integer('expires_in')->default(0)->comment('Token有效期');
            $table->integer('refresh_expires_in')->default(0)->comment('刷新Token有效期');
            $table->timestamp('token_created_at')->useCurrent()->comment('Token创建时间');
            $table->timestamp('refresh_token_created_at')->useCurrent()->comment('Token创建时间');
            $table->timestamp('created_at')->useCurrent()->comment('创建时间');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新时间');
            $table->timestamp('deleted_at')->nullable()->comment('删除时间');
            $table->comment('棒鸡账号授权表');
            $table->index(['user_id',], 'user_id');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_general_ci';
        });
    }

    public function down()
    {
        Schema::dropIfExists('bungie_bind');
    }
};
