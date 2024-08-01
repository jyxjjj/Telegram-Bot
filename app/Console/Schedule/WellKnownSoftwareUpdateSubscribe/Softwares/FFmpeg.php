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

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares;

use App\Common\Config;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class FFmpeg implements SoftwareInterface
{
    /**
     * @param int $chat_id
     * @param string $version
     * @return array
     */
    #[ArrayShape([
        'chat_id' => 'int',
        'text' => 'string',
        'reply_markup' => InlineKeyboard::class,
    ])]
    public function generateMessage(int $chat_id, string $version): array
    {
        $emoji = Common::emoji();
        $message = [
            'chat_id' => $chat_id,
            'text' => "$emoji A new version of FFmpeg($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View on gyan.dev',
            'url' => 'https://www.gyan.dev/ffmpeg/builds/#release-builds',
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => 'View on GitHub',
            'url' => "https://github.com/FFmpeg/FFmpeg/releases/tag/n$version",
        ]);
        $button3 = new InlineKeyboardButton([
            'text' => 'Windows Shared Build',
            'url' => 'https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-full-shared.7z',
        ]);
        $button4 = new InlineKeyboardButton([
            'text' => 'Windows Static Build',
            'url' => 'https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-full.7z',
        ]);
        $message['reply_markup']->addRow($button1, $button2);
        $message['reply_markup']->addRow($button3, $button4);
        return $message;
    }

    /**
     * @return string
     * @throws ConnectionException
     */
    public function getVersion(): string
    {
        return $this->getVersionString();
    }

    /**
     * @return string
     * @throws ConnectionException
     */
    public function getVersionString(): string
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-FFmpeg-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->get('https://www.gyan.dev/ffmpeg/builds/release-version');
        if ($get->status() == 200) {
            return $get->body();
        }
        return '0.0.0';
    }
}
