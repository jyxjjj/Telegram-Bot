<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
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

use App\Common\RequestHelper;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use Illuminate\Http\Client\ConnectionException;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class Laravel implements SoftwareInterface
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
            'text' => "$emoji A new version of Laravel($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => "https://github.com/laravel/framework/releases/tag/v$version",
        ]);
        $message['reply_markup']->addRow($button1);
        return $message;
    }

    /**
     * @return string
     * @throws ConnectionException
     */
    public function getVersion(): string
    {
        $data = $this->getJson();
        if (!is_array($data)) {
            return Common::getLastVersion(Software::Laravel);
        }
        $version = '0.0.0';
        foreach ($data as $branch) {
            $versionstring = str_replace('v', '', $branch['name']);
            if (version_compare($versionstring, $version, '>')) {
                $version = $versionstring;
            }
        }
        return $version;
    }

    /**
     * @return array|int|false
     * @throws ConnectionException
     */
    private function getJson(): array|int|false
    {
        $last_modified = Common::getLastModified(Software::Laravel);
        if ($last_modified) {
            $headers['If-Modified-Since'] = $last_modified;
        }
        $get = RequestHelper::getInstance()
            ->withHeaders($headers ?? [])
            ->accept('application/vnd.github+json')
            ->withToken(env('GITHUB_TOKEN'))
            ->get('https://api.github.com/repos/laravel/framework/tags?per_page=5');
        if ($get->status() == 200) {
            return $get->json();
        }
        if ($get->status() == 304) {
            return 304;
        }
        return false;
    }
}
