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

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class DeleteMessageWithKeyJob extends BaseQueue
{
    private array $data;
    private string $key;

    /**
     * @param array $data
     * @param string $key
     * @param int $delay
     */
    public function __construct(array $data, string $key, int $delay = 60)
    {
        parent::__construct();
        $this->data = $data;
        $this->key = $key;
        $this->delay($delay);
    }

    /**
     * @throws TelegramException
     */
    public function handle(): void
    {
        BotCommon::getTelegram();
        $messageId = Cache::get($this->key);
        if ($messageId) {
            $this->data['message_id'] = $messageId;
            $serverResponse = Request::deleteMessage($this->data);
            if (!$serverResponse->isOk()) {
                $errorCode = $serverResponse->getErrorCode();
                $errorDescription = $serverResponse->getDescription();
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
