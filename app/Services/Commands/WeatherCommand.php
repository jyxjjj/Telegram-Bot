<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
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

namespace App\Services\Commands;

use App\Common\BotCommon;
use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
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
        // message info
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();

        // init array
        $data = [
            'chat_id' => $chatId,
            'text' => '',
            'reply_to_message_id' => $messageId,
        ];

        // admin detection
        $notAdmin = !BotCommon::isAdmin($message);
        if ($notAdmin) {
            $data['text'] = 'This command is only available to administrators.';
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        // Degree Celsius
        $symbol = hex2bin('E2') . hex2bin('84') . hex2bin('83');

        // live
        $liveWeather = Http::withHeaders(Config::CURL_HEADERS)
            ->connectTimeout(10)
            ->timeout(10)
            ->retry(3, 1000, throw: false)
            ->baseUrl('https://restapi.amap.com/v3/')
            ->withQueryParameters([
                    'key' => env('AMAP_KEY'),
                    'city' => 320105,
                    'extensions' => 'base',
                    'output' => 'json',
                ]
            )
            ->get('weather/weatherInfo');
        $liveWeather = $liveWeather->json();
        if (isset($liveWeather['status']) && $liveWeather['status'] == '1') {
            if (isset($liveWeather['infocode']) && $liveWeather['infocode'] == '10000') {
                $data['text'] .= "Data Source: <a href=\"https://lbs.amap.com\">AMap</a>\n";
                $data['text'] .= "江苏省南京市建邺区(320105)\n";
                $data['text'] .= "Update Time: {$liveWeather['lives'][0]['reporttime']}\n";
                $data['text'] .= "实时天气: {$liveWeather['lives'][0]['weather']}\n";
                $data['text'] .= "实时温度: {$liveWeather['lives'][0]['temperature']}$symbol\n";
                $data['text'] .= "实时湿度: {$liveWeather['lives'][0]['humidity']}%\n";
                $data['text'] .= "实时风向: {$liveWeather['lives'][0]['winddirection']}\n";
                $data['text'] .= "实时风力: {$liveWeather['lives'][0]['windpower']}级\n";
            } else {
                $data['text'] .= '获取实时天气数据失败';
            }
        } else {
            $data['text'] .= '实时天气数据接口调用失败';
        }
        $this->dispatch(new SendMessageJob($data, null, 0));
        $data['text'] = '';
        // forecast
        $forecastWeather = Http::withHeaders(Config::CURL_HEADERS)
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
        $forecastWeather = $forecastWeather->json();
        if (isset($forecastWeather['status']) && $forecastWeather['status'] == '1') {
            if (isset($forecastWeather['infocode']) && $forecastWeather['infocode'] == '10000') {
                $data['text'] .= "Data Source: <a href=\"https://lbs.amap.com\">AMap</a>\n";
                $data['text'] .= "江苏省南京市建邺区(320105)\n";
                $data['text'] .= "Update Time: {$forecastWeather['forecasts'][0]['reporttime']}\n";
                foreach ($forecastWeather['forecasts'][0]['casts'] as $cast) {
                    $data['text'] .= "日期: {$cast['date']} 周{$this->toCNWeek($cast['week'])}\n";
                    $data['text'] .= "白天天气: {$cast['dayweather']}\n";
                    $data['text'] .= "白天温度: {$cast['daytemp']}$symbol\n";
                    $data['text'] .= "白天风向: {$cast['daywind']}\n";
                    $data['text'] .= "白天风力: {$cast['daypower']}级\n";
                    $data['text'] .= "夜间天气: {$cast['nightweather']}\n";
                    $data['text'] .= "夜间温度: {$cast['nighttemp']}$symbol\n";
                    $data['text'] .= "夜间风向: {$cast['nightwind']}\n";
                    $data['text'] .= "夜间风力: {$cast['nightpower']}级\n";
                    $data['text'] .= "----------------\n";
                }
                $data['text'] = rtrim($data['text'], "-\n");
            } else {
                $data['text'] .= '获取预报天气数据失败';
            }
        } else {
            $data['text'] .= '预报天气数据接口调用失败';
        }
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function toCNWeek(string $week): string
    {
        return match ($week) {
            '0', '7' => '日',
            '1' => '一',
            '2' => '二',
            '3' => '三',
            '4' => '四',
            '5' => '五',
            '6' => '六',
            default => '',
        };
    }
}
