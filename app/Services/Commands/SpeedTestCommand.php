<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
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
use App\Jobs\SendPhotoJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class SpeedTestCommand extends BaseCommand
{
    public string $name = 'speedtest';
    public string $description = 'Show the speed of the server of the bot';
    public string $usage = '/speedtest';
    public bool $admin = true;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $server = $this->getBestServer();
        $download = $this->download($server);
        $upload = $this->upload($server);
        $share = $this->share($download, $upload, $server);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'photo' => $share,
            'caption' => "<b>SpeedTest</b>\n",
        ];
        $data['caption'] .= "<b>Server</b>: <code>{$server['name']}</code>\n";
        $data['caption'] .= "<b>Sponsor</b>: <code>{$server['sponsor']}</code>\n";
        $data['caption'] .= "<b>Upload</b>: <code>$upload</code> Kbps\n";
        $data['caption'] .= "<b>Download</b>: <code>$download</code> Kbps\n";
        $data['caption'] .= "<b>Latency</b>: <code>{$server['latency']}</code>\n";
        $this->dispatch(new SendPhotoJob($data, 0));
    }

    private function getBestServer(): array
    {
        $servers = $this->getServers();
        foreach ($servers as &$server) {
            $random = openssl_random_pseudo_bytes(8);
            $random = bin2hex($random);
            $url = "{$server['protocol']}://{$server['host']}/speedtest/latency.txt?r=$random";
            $start = Carbon::now()->getTimestampMs();
            for ($i = 0; $i < 10; $i++) {
                try {
                    Http::withHeaders(Config::CURL_HEADERS)
                        ->timeout(1)
                        ->get($url);
                } catch (Throwable) {

                }
            }
            $end = Carbon::now()->getTimestampMs();
            $latency = $end - $start;
            $server['latency'] = $latency / 10;
        }
        usort($servers, fn($a, $b) => $a['latency'] <=> $b['latency']);
        return $servers[0];
    }

    private function getServers(): array
    {
        $data = Http::
        withHeaders(Config::CURL_HEADERS)
            ->get('https://www.speedtest.net/speedtest-servers.php')
            ->body();
        if (strlen($data) < 10) {
            return [];
        }
        $data = (array)simplexml_load_string($data);
        $data = json_encode($data);
        $data = json_decode($data, true);
        $server = [];
        foreach ($data['servers']['server'] as $s) {
            $server[] = $s['@attributes'];
        }
        for ($i = 0; $i < count($server); $i++) {
            if (str_starts_with('https', $server[$i]['url'])) {
                $server[$i]['protocol'] = 'https';
            } else {
                $server[$i]['protocol'] = 'http';
            }
        }
        return $server;
    }

    private function download($server): float
    {
        $random = openssl_random_pseudo_bytes(16);
        $random = bin2hex($random);
        $url = "{$server['protocol']}://{$server['host']}/download?size=250000&r=0.$random";
        $start = Carbon::now()->getTimestampMs();
        for ($i = 0; $i < 20; $i++) {
            try {
                Http::withHeaders(Config::CURL_HEADERS)
                    ->get($url);
            } catch (Throwable) {
                return -1;
            }
        }
        $end = Carbon::now()->getTimestampMs();
        $time = $end - $start;
        return number_format(5000000 / $time * 1000 / 8, 4, '.', '');
    }

    private function upload($server): float
    {
        $random = openssl_random_pseudo_bytes(16);
        $random = bin2hex($random);
        $url = "{$server['url']}?r=0.$random";
        $data = openssl_random_pseudo_bytes(50000);
        $start = Carbon::now()->getTimestampMs();
        for ($i = 0; $i < 20; $i++) {
            try {
                Http::withHeaders(Config::CURL_HEADERS)
                    ->withBody($data, 'image/jpeg')
                    ->post($url);
            } catch (Throwable) {
                return -1;
            }
        }
        $end = Carbon::now()->getTimestampMs();
        $time = $end - $start;
        return number_format(1000000 / $time * 1000 / 8, 4, '.', '');
    }

    private function share($download, $upload, $server): string
    {
        $url = "https://www.speedtest.net/api/api.php";
        $headers = array_merge(Config::CURL_HEADERS, ['Referer' => 'https://c.speedtest.net/flash/speedtest.swf']);
        $hash = md5("{$server['latency']}-$upload-$download-297aae72");
        $data = [
            "recommendedserverid={$server['id']}",
            "ping={$server['latency']}",
            "download=$download",
            "upload=$upload",
            'screenresolution=',
            'promo=',
            'screendpi=',
            "testmethod={$server['protocol']}",
            "hash=$hash",
            'touchscreen=none',
            'startmode=pingselect',
            'accuracy=1',
            'bytesreceived=5000000',
            'bytessent=5000000',
            "serverid={$server['id']}",
        ];
        $data = implode('&', $data);
        $data = Http::withHeaders($headers)
            ->withBody($data, 'application/x-www-form-urlencoded')
            ->post($url)
            ->body();
        $data = explode('&', $data);
        foreach ($data as $dd) {
            $dd = explode('=', $dd);
            if ($dd[0] == 'resultid') {
                return "https://www.speedtest.net/result/$dd[1].png";
            }
        }
        return 'https://www.speedtest.net/result/1.png';
    }
}
