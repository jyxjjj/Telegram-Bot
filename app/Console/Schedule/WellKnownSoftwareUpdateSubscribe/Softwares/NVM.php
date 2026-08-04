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

class NVM implements SoftwareInterface
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
            'text' => "$emoji A new version of Node Version Manager($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button = new InlineKeyboardButton([
            'text' => 'View',
            'url' => 'https://github.com/nvm-sh/nvm',
        ]);
        $message['reply_markup']->addRow($button);
        return $message;
    }

    /**
     * @return string|null
     * @throws ConnectionException
     */
    public function getVersion(): ?string
    {
        $data = $this->getJson();
        if ($data === 304) {
            return Common::getLastVersion(Software::NVM) ?: null;
        }
        if (!is_array($data)) {
            return null;
        }
        $version = null;
        foreach ($data as $branch) {
            if (!is_array($branch) || !isset($branch['name']) || !is_string($branch['name'])) {
                continue;
            }
            if (preg_match('/^v(\d+\.\d+\.\d+)$/D', $branch['name'], $matches) === 1) {
                if ($version === null || version_compare($matches[1], $version, '>')) {
                    $version = $matches[1];
                }
            }
        }
        return $version;
    }

    /**
     * @return array|int|null
     * @throws ConnectionException
     */
    private function getJson(): array|int|null
    {
        $last_modified = Common::getLastModified(Software::NVM);
        if ($last_modified) {
            $headers['If-Modified-Since'] = $last_modified;
        }
        $get = RequestHelper::getInstance()
            ->withHeaders($headers ?? [])
            ->accept('application/vnd.github+json')
            ->withToken(env('GITHUB_TOKEN'))
            ->get('https://api.github.com/repos/nvm-sh/nvm/tags?per_page=100');
        if ($get->status() === 304) {
            return 304;
        }
        if ($get->status() !== 200) {
            return null;
        }
        $data = $get->json();
        if (!is_array($data)) {
            return null;
        }
        Common::cacheLastModified(Software::NVM, $get->header('last-modified'));
        return $data;
    }
}
