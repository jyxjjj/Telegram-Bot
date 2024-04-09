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
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendPhotoJob extends BaseQueue
{
    private array $data;
    private int $delete;

    /**
     * @param array $data
     * @param int $delete
     */
    public function __construct(array $data, int $delete = 60)
    {
        parent::__construct();
        $this->data = array_merge($data, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        $this->delete = $delete;
    }

    /**
     * @throws TelegramException
     */
    public function handle(): void
    {
        BotCommon::getTelegram();
        if (isset($this->data['is_file']) && $this->data['is_file']) {
            $this->data['photo'] = Request::encodeFile($this->data['photo']);
            unset($this->data['is_file']);
        }
        $serverResponse = Request::sendPhoto($this->data);
        if ($serverResponse->isOk()) {
            if ($this->delete !== 0) {
                /** @var Message $sendResult */
                $sendResult = $serverResponse->getResult();
                $messageId = $sendResult->getMessageId();
                $data = [
                    'chat_id' => $this->data['chat_id'],
                    'message_id' => $messageId,
                ];
                DeleteMessageJob::dispatch($data, $this->delete);
            }
        } else {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Forbidden: bot was blocked by the user' &&
                $errorDescription != 'Forbidden: bot can\'t initiate conversation with a user' &&
                $errorDescription != 'Forbidden: bot was kicked from the supergroup chat'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
