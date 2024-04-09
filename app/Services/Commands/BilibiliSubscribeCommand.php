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

namespace App\Services\Commands;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Models\TBilibiliSubscribes;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BilibiliSubscribeCommand extends BaseCommand
{
    public string $name = 'bilibilisubscribe';
    public string $description = 'subscribe bilibili videos of an UP';
    public string $usage = '/bilibilisubscribe {USERID}';
    public string $version = '2.0.0';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $mid = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        //#region Detect Chat Type
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data['text'] .= "<b>Error</b>: This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Detect Admin Rights
        $admins = TChatAdmins::getChatAdmins($chatId);
        $userId = $message->getFrom()->getId();
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "<b>Error</b>: You should be an admin of this chat to use this command.\n\n";
            $data['text'] .= "<b>Warning</b>: This command can be used by people who was an admin before update admin list.\n\n";
            $data['text'] .= "<b>Notice</b>: Send /updatechatadministrators to update chat admin list.\n\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Check params
        if (!is_numeric($mid)) {
            $data['text'] .= "Invalid mid.\n";
            $data['text'] .= "<b>Usage</b>: /bilibilisubscribe mid.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        $url = "https://api.bilibili.com/x/v2/medialist/resource/list?type=1&biz_id=$mid&ps=1";
        $json = $this->getJson($url);
        $author = $this->getAuthorName($json);
        //#region Check params by Server
        if ($json == null) {
            $data['text'] .= "Network error.\n";
            $data['text'] .= "Please retry.\n";
            $data['text'] .= "You can click the text below to copy your command.\n";
            $data['text'] .= "<code>/bilibilisubscribe $mid</code>\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        if ($json['code'] != 0) {
            $data['text'] .= "<b>Error</b>: Bilibili Server returned error.\n";
            $data['text'] .= "{$json['message']}\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        if (TBilibiliSubscribes::addSubscribe($chatId, $mid)) {
            $data['text'] .= "<b>Notice</b>: This feature will send a message to this chat when a new video is available.\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "Author: <code>$author</code>\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "Subscribe successfully.\n";
        } else {
            $data['text'] .= "<b>Error</b>: Subscribe failed.\n";
            $data['text'] .= "One possibility is that this chat has already subscribed this mid.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }

    /**
     * @param string $link
     * @return array|null
     */
    private function getJson(string $link): ?array
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-B23-Subscriber/$this->version";
        return Http::
        withHeaders($headers)
            ->get($link)
            ->json();
    }

    private function getAuthorName(array $json): string
    {
        return $json['data']['media_list'][0]['upper']['name'];
    }
}
