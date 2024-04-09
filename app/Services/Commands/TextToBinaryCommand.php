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

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class TextToBinaryCommand extends BaseCommand
{
    public string $name = 'texttobinary';
    public string $description = 'Show message text in binary';
    public string $usage = '/texttobinary {reply_to|text}';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $param = $message->getText(true);
        if ($param == '') {
            $replyTo = $message->getReplyToMessage();
            if (!$replyTo) {
                $data['text'] .= "<b>Error</b>: You should reply to a message or provide a text for using this command.\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
            $param = $replyTo->getText();
        }
        if ($param == '') {
            $data['text'] .= "<b>Error</b>: You should reply to a message or provide a text for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        if (strlen($param) < 1) {
            $data['text'] .= "<b>Error</b>: Text is too short.\n";
            $data['text'] .= "The minimum length is 1 characters.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        if (strlen($param) > 16) {
            $data['text'] .= "<b>Error</b>: Text is too long.\n";
            $data['text'] .= "The maximum length is 16 characters.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $data['parse_mode'] = '';
        $data['text'] = strtoupper(bin2hex($param));

        $this->dispatch(new SendMessageJob($data));
    }
}
