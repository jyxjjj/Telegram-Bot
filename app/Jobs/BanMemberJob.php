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
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class BanMemberJob extends BaseQueue
{
    /**
     * @param array{chat_id: int, message_id: int, user_id: int} $data
     */
    public function __construct(private array $data)
    {
    }

    /**
     * @throws TelegramException
     */
    public function handle(): void
    {
        BotCommon::getTelegram();
        $response = Request::banChatMember([
            'chat_id' => $this->data['chat_id'],
            'user_id' => $this->data['user_id'],
            'revoke_messages' => true,
        ]);

        $sender = [
            'chat_id' => $this->data['chat_id'],
            'reply_to_message_id' => $this->data['message_id'],
            'text' => '',
        ];
        if ($response->isOk()) {
            $sender['text'] .= "<b>User banned.</b>\n";
            $sender['text'] .= "<b>User ID</b>: <a href='tg://user?id={$this->data['user_id']}'>{$this->data['user_id']}</a>\n";
        } else {
            $sender['text'] .= "<b>Error banning user.</b>\n";
            $sender['text'] .= "<b>Error Code</b>: <code>{$response->getErrorCode()}</code>\n";
            $sender['text'] .= "<b>Error Msg</b>: <code>{$response->getDescription()}</code>\n";
        }
        SendMessageJob::dispatch($sender, null, 0);
    }
}
