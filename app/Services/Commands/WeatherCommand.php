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

use App\Common\BotCommon;
use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class WeatherCommand extends BaseCommand
{
    public string $name = 'weather';
    public string $description = 'Weather of NKG, CN';
    public string $usage = '/weather';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     * @throws ConnectionException
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'text' => '',
            'reply_to_message_id' => $messageId,
        ];
        $notAdmin = !BotCommon::isAdmin($message);
        if ($notAdmin) {
            $data['text'] = 'This command is only available to administrators.';
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        $result = Http::withHeaders(Config::CURL_HEADERS)
            ->connectTimeout(10)
            ->timeout(10)
            ->retry(3, 1000, throw: false)
            ->baseUrl('https://restapi.amap.com/v3/')
            ->withQueryParameters([
                    'key' => env('AMAP_KEY'),
                    'city' => 320105,
                    'extensions' => 'all',
                    'output' => 'json',
                ]
            )
            ->get('weather/weatherInfo');
        Log::debug($result);
//        $data['text'] .= '';
//        $this->dispatch(new SendMessageJob($data));
    }
}
