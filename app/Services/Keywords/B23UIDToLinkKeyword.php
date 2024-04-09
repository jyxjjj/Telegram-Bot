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

namespace App\Services\Keywords;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseKeyword;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class B23UIDToLinkKeyword extends BaseKeyword
{
    public string $name = 'b23 uid to link';
    public string $description = 'generate link from b23 uid';
    protected string $pattern = '/UID:(\d+)/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $text = $message->getText(true) ?? $message->getCaption();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $this->handle($text, $data);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function handle(string $text, array &$data): void
    {
        if (preg_match_all($this->pattern, $text, $matches)) {
            $data['text'] .= "Bilibili UID Detected\n";
            $data['text'] .= "<b>Warning</b>: UID detected does not necessarily mean Bilibili UID\n\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (isset($matches[1][0])) {
                $data['text'] .= "UID: <code>{$matches[1][0]}</code>\n";
                $data['text'] .= "Link: <code>https://space.bilibili.com/{$matches[1][0]}</code>\n";
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "UID: {$matches[1][0]}",
                        'url' => "https://space.bilibili.com/{$matches[1][0]}",
                    ])
                );
            }
        }
    }

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }
}
