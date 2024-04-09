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
            ->baseUrl('https://api.caiyunapp.com/v2.6/{token}/{pos}/')
            ->withUrlParameters([
                    'token' => env('CAIYUN_WEATHER_API_TOKEN'),
                    'pos' => '118.7271427,32.0348853',
                ]
            )
            ->get('daily?dailysteps=1&unit=metric:v2');
        $result = $result->json('result');
        @$str = "Location: 118.7271427, 32.0348853 NKG, CN\n";
        @$str .= "日出日落：{$result['daily']['astro'][0]['sunrise']['time']} ~ {$result['daily']['astro'][0]['sunset']['time']}\n";
        @$str .= "天气：{$this->translate($result['daily']['skycon'][0]['value'])}\n";
        @$str .= "降水量：{$result['daily']['precipitation'][0]['min']} ~ {$result['daily']['precipitation'][0]['max']} AVG {$result['daily']['precipitation'][0]['avg']}\n";
        @$str .= "降水概率：{$result['daily']['precipitation'][0]['probability']}\n";
        @$str .= "气温：{$result['daily']['temperature'][0]['min']} ~ {$result['daily']['temperature'][0]['max']} AVG {$result['daily']['temperature'][0]['avg']}\n";
        @$str .= "能见度：{$result['daily']['visibility'][0]['min']} ~ {$result['daily']['visibility'][0]['max']} AVG {$result['daily']['visibility'][0]['avg']}\n";
        @$str .= "PM2.5：{$result['daily']['air_quality']['pm25'][0]['min']} ~ {$result['daily']['air_quality']['pm25'][0]['max']} AVG {$result['daily']['air_quality']['pm25'][0]['avg']}\n";
        @$str .= "舒适度：{$result['daily']['life_index']['comfort'][0]['desc']}\n";
        @$str .= "紫外线：{$result['daily']['life_index']['ultraviolet'][0]['desc']}\n";
        @$str .= "穿衣指数：{$result['daily']['life_index']['dressing'][0]['desc']}\n";
        $data['text'] .= $str;
        $this->dispatch(new SendMessageJob($data));
    }

    private function translate(string $skycon): string
    {
        return match ($skycon) {
            'CLEAR_DAY', 'CLEAR_NIGHT' => '晴',
            'PARTLY_CLOUDY_DAY', 'PARTLY_CLOUDY_NIGHT' => '多云',
            'CLOUDY' => '阴',
            'LIGHT_HAZE' => '轻度雾霾',
            'MODERATE_HAZE' => '中度雾霾',
            'HEAVY_HAZE' => '重度雾霾',
            'LIGHT_RAIN' => '小雨',
            'MODERATE_RAIN' => '中雨',
            'HEAVY_RAIN' => '大雨',
            'STORM_RAIN' => '暴雨',
            'FOG' => '雾',
            'LIGHT_SNOW' => '小雪',
            'MODERATE_SNOW' => '中雪',
            'HEAVY_SNOW' => '大雪',
            'STORM_SNOW' => '暴雪',
            'DUST' => '浮尘',
            'SAND' => '沙尘',
            'WIND' => '大风',
        };
    }
}
