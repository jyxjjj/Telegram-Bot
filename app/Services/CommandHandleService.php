<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
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

use App\Common\BotCommon;
use App\Jobs\SendMessageJob;
use App\Models\TStarted;
use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Throwable;

class CommandHandleService extends BaseService
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
        $senderId = $message->getFrom()->getId();
        $isStarted = TStarted::getUser($senderId);
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $notAdmin = !BotCommon::isAdmin($message);
        $notPrivate = !$message->getChat()->isPrivateChat();
        $sendCommand = $message->getCommand();
        $files = glob(app_path('Services/Commands/*Command.php'));
        foreach ($files as $fileName) {
            $command = basename($fileName, '.php');
            $command_class = "App\\Services\\Commands\\$command";
            try {
                $command_class = app()->make($command_class);
            } catch (Throwable) {
                continue;
            }
            if ($command_class->name != $sendCommand) { // Detect if command matches
                continue;
            }
            if ($command_class->admin && $notAdmin) { // Detect if command is admin only
                $data = [
                    'chat_id' => $senderId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command is admin only.\n";
                !$isStarted && $data['chat_id'] = $chatId;
                !$isStarted && $data['text'] .= "You should send a message to me in private, so that i can send message to you.\n";
                $this->dispatch(new SendMessageJob($data));
                return true;
            }
            if ($command_class->private && $notPrivate) {// Detect if command is private only
                $data = [
                    'chat_id' => $senderId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command needs to be sent in a private chat.\n";
                !$isStarted && $data['chat_id'] = $chatId;
                !$isStarted && $data['text'] .= "You should send a message to me in private, so that i can send message to you.\n";
                $this->dispatch(new SendMessageJob($data));
                return true;
            }
            $command_class->execute($message, $telegram, $updateId); // Execute command
            return true;
        }
        return false;
    }
}
