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

namespace App\Services\Commands;

use App\Common\RequestHelper;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class PSLSearchCommand extends BaseCommand
{
    public string $name = 'psl';
    public string $description = 'Search PSL if the domain you provided is a PSL domain.';
    public string $usage = '/psl {domain}';

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
        if (empty($param)) {
            $data['text'] = 'Please provide a domain.';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $domain = $param;
        try {
            $resp = RequestHelper::getInstance()
                ->get('https://raw.githubusercontent.com/publicsuffix/list/master/public_suffix_list.dat');
            if ($resp->failed()) {
                $result = "Failed to fetch PSL data.\n";
            } else {
                $matched = $resp->body();
                $matched = explode("\n", $matched);
                // Remove comments and empty lines
                $matched = array_filter($matched, fn($line) => !empty($line) && $line[0] !== '/' && $line[0] !== '!' && $line[0] !== '[' && str_contains($line, '.'));
                // Remove leading dots
                $matched = array_map(fn($line) => ltrim($line, '*.'), $matched);
                // Filter by domain
                $matched = array_filter($matched, fn($line) => $line === $domain || str_ends_with($domain, '.' . $line));
                if (empty($matched)) {
                    $result = "PSL Domain: <b>No</b>\n";
                } else {
                    $result = "PSL Domain: <b>Yes</b>\n";
                    $result .= 'Matched: ' . "\n" . implode("\n", $matched);
                }
            }
        } catch (Throwable) {
            $result = "Failed to fetch PSL data.\n";
        }
        $data['text'] = $result;
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
