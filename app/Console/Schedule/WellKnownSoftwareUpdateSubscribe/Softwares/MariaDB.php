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
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use Illuminate\Http\Client\ConnectionException;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class MariaDB implements SoftwareInterface
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
            'text' => "$emoji A new version of MariaDB($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => "https://mariadb.org/download/?t=mariadb&p=mariadb&r=$version&os=source",
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => 'Download',
            'url' => "https://downloads.mariadb.org/rest-api/mariadb/$version/mariadb-$version.tar.gz",
        ]);
        $message['reply_markup']->addRow($button1, $button2);
        return $message;
    }

    /**
     * @return string
     * @throws ConnectionException
     */
    public function getVersion(): string
    {
        $majors = $this->getMajor();
        $majors = $majors['major_releases'];
        $major = 0;
        $minor = 0;
        foreach ($majors as $release) {
            if ($release['release_status'] == 'Stable') {
                $release_id = $release['release_id'];
                if (preg_match('/^(\d+)\.(\d+)$/i', $release_id, $matches)) {
                    if ($matches[1] > $major) {
                        $major = $matches[1];
                        $minor = $matches[2];
                    } elseif ($matches[1] == $major && $matches[2] > $minor) {
                        $minor = $matches[2];
                    }
                }
            }
        }
        $release_id = "$major.$minor";
        $release = $this->getLatest($release_id);
        $release = $release['releases'];
        $version = '';
        foreach ($release as $key => $value) {
            $version = $key;
            break;
        }
        return $version;
    }

    /**
     * @return array
     * @throws ConnectionException
     */
    private function getMajor(): array
    {
        return RequestHelper::getInstance()
            ->get('https://downloads.mariadb.org/rest-api/mariadb/')
            ->json();
    }

    /**
     * @param string $release_id
     * @return array
     * @throws ConnectionException
     */
    private function getLatest(string $release_id): array
    {
        return RequestHelper::getInstance()
            ->get("https://downloads.mariadb.org/rest-api/mariadb/$release_id/latest")
            ->json();
    }
}
