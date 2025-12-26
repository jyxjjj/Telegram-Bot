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

class GitHubIssueIDToLinkKeyword extends BaseKeyword
{
    public string $name = 'github issue id to link';
    public string $description = 'generate link from github issue id';
    protected string $pattern = '/(?<!\S)#(\d+)/u';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        if ($chatId != -1002573155438 && $chatId != -4971290320) {
            return;
        }
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

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }

    private function handle(string $text, array &$data): void
    {
        if (preg_match_all($this->pattern, $text, $matches)) {
            $data['text'] .= "GitHub Issue ID Detected\n";
            $data['text'] .= "<b>注意</b>: 由于检测方式原因，您发送的可能不是GitHub Issue ID。\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (isset($matches[1][0])) {
                $data['text'] .= "您发送的ID是:\n";
                $data['text'] .= "<blockquote>#{$matches[1][0]}</blockquote>\n";

                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "讨",
                        'url' => "https://github.com/orgs/OpenListTeam/discussions/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => '文',
                        'url' => "https://github.com/OpenListTeam/OpenList-Docs/pull/{$matches[1][0]}",
                    ]),
                );
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "后",
                        'url' => "https://github.com/OpenListTeam/OpenList/issues/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => "前",
                        'url' => "https://github.com/OpenListTeam/OpenList-Frontend/issues/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => 'API',
                        'url' => "https://github.com/OpenListTeam/OpenList-APIPages/issues/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => '桌',
                        'url' => "https://github.com/OpenListTeam/OpenList-Desktop/issues/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => '手',
                        'url' => "https://github.com/OpenListTeam/OpenList-Mobile/issues/{$matches[1][0]}",
                    ]),
                );
            }
        }
    }
}
