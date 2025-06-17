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

namespace App\Services\Keywords;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseKeyword;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class GoogleFontsToLinkKeyword extends BaseKeyword
{
    public string $name = 'google fonts to link';
    public string $description = 'generate link from google fonts';
    protected string $pattern = '/(Noto\s*(Color)?\s*Emoji|Noto\s*Sans\s*(SC)?|JetBrains\s*Mono\s*(NL)?|Material\s*(Icon|Symbol)s?)/i';

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
            $data['text'] .= "Google Fonts Detected\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (isset($matches[1][0])) {
                $data['text'] .= "您似乎发送了一个可被检测的字体信息: <code>{$matches[1][0]}</code>\n\n";
                $data['text'] .= "您是不是在找：\n";
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "Noto Color Emoji",
                        'url' => "https://fonts.google.com/noto/specimen/Noto+Color+Emoji",
                    ]),
                    new InlineKeyboardButton([
                        'text' => "Noto Emoji",
                        'url' => "https://fonts.google.com/noto/specimen/Noto+Emoji",
                    ])
                );
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "Noto Sans SC",
                        'url' => "https://fonts.google.com/noto/specimen/Noto+Sans+SC",
                    ]),
                    new InlineKeyboardButton([
                        'text' => "Noto Sans",
                        'url' => "https://fonts.google.com/noto/specimen/Noto+Sans",
                    ])
                );
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "JetBrains Mono",
                        'url' => "https://fonts.google.com/specimen/JetBrains+Mono",
                    ]),
                );
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => 'Material Icons',
                        'url' => 'https://fonts.google.com/icons?icon.set=Material+Icons',
                    ]),
                    new InlineKeyboardButton([
                        'text' => 'Material Symbols',
                        'url' => 'https://fonts.google.com/icons?icon.set=Material+Symbols',
                    ]),
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
