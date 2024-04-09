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

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseKeyword;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AMapTrackerRemoverKeyword extends BaseKeyword
{
    public string $name = 'AMap tracker remover';
    public string $description = 'Remove AMap tracker from surl link';
    public string $version = '1.0.2';
    protected string $pattern = '/(surl\.amap\.com)/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $text = $message->getText();
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
        $pattern = '/(http(s)?:\/\/)?(surl\.amap\.com)\/?[a-zA-Z\d]+/';
        if (preg_match_all($pattern, $text, $matches)) {
            $pattern = '/https:\/\/(www|wb)\.amap\.com\/\?p=([a-zA-Z\d]+),(\d+\.\d+),(\d+\.\d+)/';
            $data['text'] .= "AMap Tracker Remover v$this->version [Beta]\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (count($matches[0]) > 3) {
                $count = 3;
            } else {
                $count = count($matches[0]);
            }
            for ($i = 0; $i < $count; $i++) {
                $link = $matches[0][$i];
                $this->normalizeLink($link);
                $location = $this->getLocation($link);
                $location = urldecode($location);
                if (preg_match($pattern, $location, $matchedLocation)) {
                    $location = "https://www.amap.com/place/$matchedLocation[2]";
                    $data['text'] .= "<code>$location</code>\n";
                    $button = new InlineKeyboardButton([
                        'text' => $location,
                        'url' => $location,
                    ]);
                    $data['reply_markup']->addRow($button);
                }
            }
        }
    }

    private function normalizeLink(string &$link): void
    {
        if (str_starts_with($link, 'http://')) {
            str_replace('http://', 'https://', $link);
        }
        if (str_starts_with($link, 'surl.amap.com')) {
            $link = "https://$link";
        }
    }

    private function getLocation(string $link): string
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-AMap-Link-Tracker-Remover/$this->version";
        return Http::
        connectTimeout(10)
            ->timeout(10)
            ->retry(3, 1000, throw: false)
            ->withHeaders($headers)
            ->withoutRedirecting()
            ->get($link)
            ->header('Location');
    }

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }
}
