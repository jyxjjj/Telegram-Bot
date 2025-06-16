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
    protected string $pattern = '/ #(\d{1,5})/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        if ($chatId != -1002573155438) {
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

    private function handle(string $text, array &$data): void
    {
        if (preg_match_all($this->pattern, $text, $matches)) {
            $data['text'] .= "GitHub Issue ID Detected\n";
            $data['text'] .= "<b>注意</b>: 由于检测方式原因，您发送的可能不是GitHub Issue ID。\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (isset($matches[1][0])) {
                $data['text'] .= "您发送的ID是: <code>{$matches[1][0]}</code>\n";
                $data['text'] .= "请根据您发送的内容自行选择链接打开，按钮中的链接可能并不对应您发送的Issue ID，故可能访问目标404或被302到其他位置。\n\n";
                $data['text'] .= "您是不是在找：\n";

                // 讨论区
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "讨论区: #{$matches[1][0]}",
                        'url' => "https://github.com/orgs/OpenListTeam/discussions/{$matches[1][0]}",
                    ]),
                );
                // 后端 两个放一行
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "后端 Issue: #{$matches[1][0]}",
                        'url' => "https://github.com/OpenListTeam/OpenList/issues/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => "后端 PR: #{$matches[1][0]}",
                        'url' => "https://github.com/OpenListTeam/OpenList/pull/{$matches[1][0]}",
                    ]),
                );
                // 前端 两个放一行
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "前端 Issue: #{$matches[1][0]}",
                        'url' => "https://github.com/OpenListTeam/OpenList-Frontend/issues/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => "前端 PR: #{$matches[1][0]}",
                        'url' => "https://github.com/OpenListTeam/OpenList-Frontend/pull/{$matches[1][0]}",
                    ]),
                );
                // API项目 两个放一行
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "API项目 Issue: #{$matches[1][0]}",
                        'url' => "https://github.com/OpenListTeam/cf-worker-api/issues/{$matches[1][0]}",
                    ]),
                    new InlineKeyboardButton([
                        'text' => "API项目 PR: #{$matches[1][0]}",
                        'url' => "https://github.com/OpenListTeam/cf-worker-api/pull/{$matches[1][0]}",
                    ]),
                );
                // 文档 PR
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "文档 PR: #{$matches[1][0]}",
                        'url' => "https://github.com/OpenListTeam/docs/pull/{$matches[1][0]}",
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
