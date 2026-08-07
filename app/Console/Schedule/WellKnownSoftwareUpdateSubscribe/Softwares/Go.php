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

class Go implements SoftwareInterface
{
    const string GQL = <<<EOF
{
  repository(owner: "golang", name: "go") {
    refs(
      refPrefix: "refs/tags/"
      last: 5
      orderBy: {field: TAG_COMMIT_DATE, direction: ASC}
    ) {
      nodes {
        name
      }
    }
  }
}
EOF;

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
            'text' => "$emoji A new version of Go($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button = new InlineKeyboardButton([
            'text' => 'View',
            'url' => 'https://github.com/golang/go',
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
        if (!is_array($data)) {
            return null;
        }
        $data = $data['data']['repository']['refs']['nodes'] ?? null;
        if (!is_array($data)) {
            return null;
        }
        $version = null;
        foreach ($data as $branch) {
            if (!is_array($branch) || !isset($branch['name']) || !is_string($branch['name'])) {
                continue;
            }
            if (preg_match('/^go(\d+\.\d+\.\d+)$/D', $branch['name'], $matches) === 1) {
                if ($version === null || version_compare($matches[1], $version, '>')) {
                    $version = $matches[1];
                }
            }
        }
        return $version;
    }

    /**
     * @return array|null
     * @throws ConnectionException
     */
    private function getJson(): ?array
    {
        $get = RequestHelper::getInstance()
            ->withToken(env('GITHUB_TOKEN'))
            ->post('https://api.github.com/graphql', [
                'query' => self::GQL,
            ]);
        if ($get->status() !== 200) {
            return null;
        }
        $data = $get->json();
        return is_array($data) ? $data : null;
    }
}
