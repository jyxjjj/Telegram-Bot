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

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class MessageHandleService extends BaseService
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
        $message = $update->getMessage();
        if ($this->ifBlocked($message)) {
            return;
        }
        $messageType = $message->getType();
        $this->addHandler('ANY', KeywordHandleService::class);
        $this->addHandler('command', CommandHandleService::class);
        $this->addHandler('sticker', StickerHandleService::class);
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
//            'dice':
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
//            'forum_topic_created':
//            'forum_topic_closed':
//            'forum_topic_reopened':
//            'voice_chat_scheduled':
//            'voice_chat_started':
//            'voice_chat_ended':
//            'voice_chat_participants_invited':
//            'web_app_data':
//            'reply_markup':
    }

    /**
     * When user was whited, return false
     * When user was blocked, return true
     * @param Message $message
     * @return bool
     */
    private function ifBlocked(Message $message): bool
    {
        $blockedUsers = [
            296672714,
            1891466551,
            447632604,
            5738737040,
            1583896650,
            5167276446,
        ];
        // 黑名单用户直接拒绝 发送拒绝消息
        if (in_array($message->getChat()->getId(), $blockedUsers)) {
            $this->dispatch(
                new SendMessageJob(
                    data: [
                        'chat_id' => $message->getChat()->getId(),
                        'parse_mode' => '',
                        'text' => 'You have been blocked',
                    ],
                    delete: 0
                )
            );
            return true;
        }
        $whitedChats = [
            env('TELEGRAM_ADMIN_USER_ID'),
            -1001344643532, // 龙缘科技
            -1001391154172, // 关于这件事
            -1002573155438, // OpenList交流群
        ];
        // 白名单用户直接通过
        if (in_array($message->getChat()->getId(), $whitedChats)) {
            return false;
        } else {
            // 其他用户需要判断是否是关于命令
            if ($message->getType() == 'command' && $message->getCommand() == 'about') {
                return false;
            }
            // 非关于命令直接拒绝
            return true;
        }
    }

    /**
     * @param string $needType
     * @param string $class
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
