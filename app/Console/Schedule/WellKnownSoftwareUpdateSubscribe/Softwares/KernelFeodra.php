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

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares;

use App\Common\Config;
use App\Common\ERR;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Throwable;

class KernelFeodra implements SoftwareInterface
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
            'text' => "$emoji A new version of FedoraKernel($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'China USTC Mirror',
            'url' => 'https://mirrors.ustc.edu.cn/fedora/updates/40/Everything/x86_64/Packages/k/',
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => 'Europe Edge Mirror',
            'url' => 'https://eu.edge.kernel.org/fedora/updates/40/Everything/x86_64/Packages/k/',
        ]);
        $message['reply_markup']->addRow($button1, $button2);
        return $message;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $baseurl = 'https://eu.edge.kernel.org/fedora/updates/40/Everything/x86_64/Packages/k/';
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-Kernel-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->accept('text/html')
            ->get($baseurl);
        $html = $get->body();
        $version = '0.0.0';
        try {
            $dom = new DOMDocument;
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            /** @var DOMNodeList $hrefs */
            $hrefs = $xpath->evaluate("/html/body//a");
            for ($i = 0; $i < $hrefs->length; $i++) {
                /** @var DOMElement $href */
                $href = $hrefs->item($i);
                $url = $href->getAttribute('href');
                if (str_starts_with($url, 'kernel-') && str_contains($url, 'core') && str_ends_with($url, '.rpm')) {
                    $versionstring = explode('-', $url)[2];
                    if ($version == '0.0.0' || version_compare($versionstring, $version, '>')) {
                        $version = $versionstring;
                    }
                }
            }
        } catch (Throwable $e) {
            ERR::log($e);
        }
        return $version;
    }
}
