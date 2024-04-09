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

namespace App\Console\Schedule;

use App\Common\Config;
use App\Common\ERR;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PixivDownloader extends Command
{
    protected $signature = 'pixiv:download';
    protected $description = 'Download Daily Rank of Pixiv';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            [$data, $date] = $this->getRanks();
            self::info("Last Update: $date");
            $data = $this->buildData($data);
            $this->saveData($date, $data);
            return self::SUCCESS;
        } catch (Throwable $e) {
            self::error("Error({$e->getCode()}):{$e->getMessage()}@{$e->getFile()}:{$e->getLine()}");
            ERR::log($e);
            return self::FAILURE;
        }
    }

    private function getRanks(): array
    {
        $headers = Config::CURL_HEADERS;
        $headers['Referer'] = 'https://www.pixiv.net/ranking.php?mode=daily';
        $data = [];
        $json['next'] = 1;
        $date = Carbon::createFromFormat('Ymd', '19700101');
        while ($json['next']) {
            self::info("Getting Page {$json['next']}");
            $url = "https://www.pixiv.net/ranking.php?mode=daily&content=illust&p={$json['next']}&format=json";
            $response = Http::withHeaders($headers)
                ->connectTimeout(10)
                ->timeout(10)
                ->retry(3, 1000, throw: false)
                ->get($url);
            $json = $response->json();
            $code = $response->status();
            if (!isset($json['contents'])) {
                self::error("Pixiv API Error: $code");
                break;
            }
            $data = array_merge($data, $json['contents']);
            $date = Carbon::createFromFormat('Ymd', $json['date']);
            sleep(1);
        }
        return [$data, $date];
    }

    private function buildData(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $replace = preg_replace('/c\/\d+x\d+\//', '', $item['url']);
            $url = $replace ?? $item['url'];
            $artwork_id = $item['illust_id'];
            $title = $item['title'];
            $author = $item['user_name'];
            $author_id = $item['user_id'];
            $artwork_url = "https://www.pixiv.net/artworks/$artwork_id";
            $author_url = "https://www.pixiv.net/users/$author_id";
            $result[] = [
                'artwork_id' => $artwork_id,
                'title' => $title,
                'author' => $author,
                'author_id' => $author_id,
                'artwork_url' => $artwork_url,
                'author_url' => $author_url,
                'url' => $url,
            ];
        }
        return $result;
    }

    private function saveData(Carbon $date, array $data): void
    {
        $storage = Storage::disk('public');
        $path = "pixiv/{$date->format('Y-m-d')}.json";
        $data = [
            'date' => $date->format('Y-m-d H:i:s'),
            'data' => $data,
        ];
        $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $storage->put($path, $data);
        self::info("Saved to {$storage->path($path)}");
    }
}
