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

namespace App\Services;

use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Throwable;

class KeywordHandleService extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return bool
     * @throws TelegramException
     */
    public function handle(Message $message, Telegram $telegram, int $updateId): bool
    {
        $files = glob(app_path('Services/Keywords/*Keyword.php'));
        foreach ($files as $fileName) {
            $handler = basename($fileName, '.php');
            $handler_class = "App\\Services\\Keywords\\$handler";
            try {
                $handler_class = app()->make($handler_class);
            } catch (Throwable) {
                continue;
            }
            $handler_class->preExecute($message) && $handler_class->execute($message, $telegram, $updateId);
        }
        return false;
    }
}
