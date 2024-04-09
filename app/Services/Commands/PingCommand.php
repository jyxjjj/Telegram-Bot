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

use App\Jobs\EditMessageTextWithKeyJob;
use App\Jobs\SendMessageWithKeyJob;
use App\Services\Base\BaseCommand;
use DESMG\RFC4122\UUID;
use DESMG\RFC792\Ping;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class PingCommand extends BaseCommand
{
    public string $name = 'ping';
    public string $description = 'Show the latency to the bot server';
    public string $usage = '/ping';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws Exception
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $key = UUID::generateUniqueID();
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'text' => 'Calculating...',
        ];
        $this->dispatch(new SendMessageWithKeyJob($data, $key, null));
        $data['text'] = '';
        $sendTime = $message->getDate();
        $sendTime = Carbon::createFromTimestamp($sendTime)->getTimestampMs();
        $startTime = Cache::get("TelegramUpdateStartTime_$updateId", 0);
        $startTime = Carbon::createFromTimestampMs($startTime)->getTimestampMs();
        $server_latency = $startTime - $sendTime;
        $telegramIP = Cache::get("TelegramIP_$updateId", '');
        $endTime = Carbon::now()->getTimestampMs();
        $message_latency = $endTime - $startTime;
        $data['text'] .= "<b>Send Time</b>: <code>$sendTime</code>\n";
        $data['text'] .= "<b>Start Time</b>: <code>$startTime</code>\n";
        $data['text'] .= "<b>End Time</b>: <code>$endTime</code>\n";
        $data['text'] .= "<b>Server Latency</b>: <code>$server_latency</code> ms\n";
        $data['text'] .= "<b>Message Latency</b>: <code>$message_latency</code> ms\n";
        $data['text'] .= "<b>Telegram Update IP</b>: <code>$telegramIP</code>\n";
        $IPs = [
            '149.154.175.53',
            '149.154.167.51',
            '149.154.175.100',
            '149.154.167.91',
            '91.108.56.130',
        ];
        $ping = new Ping();
        for ($i = 1; $i <= count($IPs); $i++) {
            $IP = $IPs[$i - 1];
            $ping->setHost($IP);
            $ping->run();
            $latency = $ping->getLatency();
            $data['text'] .= "<b>DC$i($IP) Latency</b>: <code>$latency ms</code>\n";
        }
        unset($ping);
        $this->dispatch(new EditMessageTextWithKeyJob($data, $key));
    }
}
