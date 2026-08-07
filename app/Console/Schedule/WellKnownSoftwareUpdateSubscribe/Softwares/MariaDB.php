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
 * the Free Software Foundation, version 3 of the License.
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
     * @return string|null
     * @throws ConnectionException
     */
    public function getVersion(): ?string
    {
        $majors = $this->getMajor();
        if ($majors === null || !isset($majors['major_releases']) || !is_array($majors['major_releases'])) {
            return null;
        }
        $releaseId = null;
        foreach ($majors['major_releases'] as $release) {
            if (!is_array($release) || ($release['release_status'] ?? null) !== 'Stable' || !isset($release['release_id']) || !is_string($release['release_id'])) {
                continue;
            }
            if (preg_match('/^\d+\.\d+$/D', $release['release_id']) === 1 && ($releaseId === null || version_compare($release['release_id'], $releaseId, '>'))) {
                $releaseId = $release['release_id'];
            }
        }
        if ($releaseId === null) {
            return null;
        }
        $release = $this->getLatest($releaseId);
        if ($release === null || !isset($release['releases']) || !is_array($release['releases'])) {
            return null;
        }
        $version = null;
        foreach (array_keys($release['releases']) as $candidate) {
            if (is_string($candidate) && preg_match('/^\d+\.\d+\.\d+$/D', $candidate) === 1 && ($version === null || version_compare($candidate, $version, '>'))) {
                $version = $candidate;
            }
        }
        return $version;
    }

    /**
     * @return array|null
     * @throws ConnectionException
     */
    private function getMajor(): ?array
    {
        $get = RequestHelper::getInstance()
            ->get('https://downloads.mariadb.org/rest-api/mariadb/');
        if ($get->status() !== 200) {
            return null;
        }
        $data = $get->json();
        return is_array($data) ? $data : null;
    }

    /**
     * @param string $release_id
     * @return array|null
     * @throws ConnectionException
     */
    private function getLatest(string $release_id): ?array
    {
        $get = RequestHelper::getInstance()
            ->get("https://downloads.mariadb.org/rest-api/mariadb/$release_id/latest");
        if ($get->status() !== 200) {
            return null;
        }
        $data = $get->json();
        return is_array($data) ? $data : null;
    }
}
