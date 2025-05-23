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

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use DESMG\DESMG\Sensitive;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class HideMyNumbersCommand extends BaseCommand
{
    public string $name = 'hidemynumbers';
    public string $description = 'hide, enc and hash phone, id, cards, etc..';
    public string $usage = '/hidemynumbers';

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
        $param = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        if (preg_match('/^\d{6,64}$/', $param) === 0) {
            $data['text'] = 'Invalid number';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $pass = strtoupper(bin2hex(openssl_random_pseudo_bytes(32)));
        switch (strlen($param)) {
            case 11:
                $fLen = 3;
                $eLen = 4;
                break;
            case 18:
                $fLen = 3;
                $eLen = 2;
                break;
            default:
                $fLen = 2;
                $eLen = 2;
                break;
        }
        [$plain, $hash, $encrypted] = Sensitive::encrypt($param, $pass, $fLen, $eLen);
        $data['text'] .= "Plain:\n<pre>$plain</pre>\n";
        $data['text'] .= "Hash:\n<pre>$hash</pre>\n";
        $data['text'] .= "Encrypted:\n<pre>$encrypted</pre>\n";
        $data['text'] .= "Password:\n<pre>$pass</pre>\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
