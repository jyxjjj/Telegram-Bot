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

use App\Services\Base\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class ChannelPostHandleService extends BaseService
{
    /**
     * @var array
     */
    private array $handlers;

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     * @throws BindingResolutionException
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
        $message = $update->getChannelPost();
        $messageType = $message->getType();
        $this->addHandler('command', ChannelCommandHandleService::class);
        $this->runHandler($messageType, $message, $telegram, $updateId);
//            'command':
//            'text':
//            'audio':
//            'animation':
//            'document':
//            'game':
//            'photo':
//            'sticker':
//            'video':
//            'voice':
//            'video_note':
//            'contact':
//            'location':
//            'venue':
//            'poll':
//            'new_chat_members':
//            'left_chat_member':
//            'new_chat_title':
//            'new_chat_photo':
//            'delete_chat_photo':
//            'group_chat_created':
//            'supergroup_chat_created':
//            'channel_chat_created':
//            'message_auto_delete_timer_changed':
//            'migrate_to_chat_id':
//            'migrate_from_chat_id':
//            'pinned_message':
//            'invoice':
//            'successful_payment':
//            'passport_data':
//            'proximity_alert_triggered':
//            'voice_chat_scheduled':
//            'voice_chat_started':
//            'voice_chat_ended':
//            'voice_chat_participants_invited':
//            'reply_markup':
    }

    /**
     * @param string $needType
     * @param string $class
     * @noinspection PhpSameParameterValueInspection
     */
    private function addHandler(string $needType, string $class): void
    {
        $this->handlers[] = [
            'type' => $needType,
            'class' => $class,
        ];
    }

    /**
     * @param string $type
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws BindingResolutionException
     * @throws TelegramException
     */
    private function runHandler(string $type, Message $message, Telegram $telegram, int $updateId): void
    {
        foreach ($this->handlers as $handler) {
            if ($type == $handler['type'] || $handler['type'] == '*' || $handler['type'] == 'ANY') {
                $handled = app()->make($handler['class'])->handle($message, $telegram, $updateId);
                if ($handled) {
                    return;
                }
            }
        }
    }
}
