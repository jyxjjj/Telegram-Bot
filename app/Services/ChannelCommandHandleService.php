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

namespace App\Services;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Throwable;

class ChannelCommandHandleService extends BaseService
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
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $sendCommand = $message->getCommand();
        $path = app_path('Services/ChannelCommands');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Command.php$/'
        );
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $command = str_replace('.php', '', $fileName);
            $command_class = "App\\Services\\ChannelCommands\\$command";
            try {
                $command_class = app()->make($command_class);
            } catch (Throwable) {
                continue;
            }
            if ($command_class->name != $sendCommand) { // Detect if command matches
                continue;
            }
            if ($command_class->admin) {
                $data = [
                    'chat_id' => $chatId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command doesn't support channel.\n";
                $this->dispatch(new SendMessageJob($data));
                return true;
            }
            if ($command_class->private) {
                $data = [
                    'chat_id' => $chatId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command needs to be sent in a private chat.\n";
                $this->dispatch(new SendMessageJob($data));
                return true;
            }
            $command_class->execute($message, $telegram, $updateId); // Execute command
            return true;
        }
        return false;
    }
}
