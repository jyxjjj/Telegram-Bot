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

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class IATACommand extends BaseCommand
{
    public string $name = 'iata';
    public string $description = 'Search iata info.';
    public string $usage = '/iata {Search String}';

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
        $params = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $this->find($data, trim($params));
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function find(array &$data, string $name): void
    {
        $exists = file_exists(database_path('airports.json'));
        if (!$exists) {
            $data['text'] = '<b>ERROR</b>: Cannot found airports database.';
            return;
        }
        $file = file_get_contents(database_path('airports.json'));
        $json = json_decode($file, true);
        unset($file);
        foreach ($json as $id => $airport) {
            if (str_contains(strtolower($airport['iata'] ?? ''), strtolower($name))) {
                $data['text'] = <<<EOF
Name: {$airport['name']}
IATA: {$airport['iata']}
ICAO: {$airport['icao']}
Place: {$airport['city']}, {$airport['state']}, {$airport['country']}
Location: <code>{$airport['lat']}, {$airport['lon']}, {$airport['elevation']}</code>
Timezone: {$airport['tz']}
EOF;
                $data['reply_markup'] = new InlineKeyboard([]);
                $button1 = new InlineKeyboardButton([
                    'text' => 'Show In Google Maps',
                    'url' => "https://www.google.com/maps?q={$airport['lat']},{$airport['lon']}",
                ]);
                $button2 = new InlineKeyboardButton([
                    'text' => 'Show In Apple Maps',
                    'url' => "https://maps.apple.com/?ll={$airport['lat']},{$airport['lon']}&q={$airport['name']}",
                ]);
                $button3 = new InlineKeyboardButton([
                    'text' => ($airport['tz'] == 'Asia/Shanghai' ? '' : '[Unsupported] ') . 'Show In AMap',
                    'url' => "https://ditu.amap.com/regeo?lat={$airport['lat']}&lng={$airport['lon']}&name={$airport['name']}",
                ]);
                $data['reply_markup']->addRow($button1);
                $data['reply_markup']->addRow($button2);
                $data['reply_markup']->addRow($button3);
                return;
            }
        }
        $data['text'] = "<b>ERROR</b>: Cannot found airport which name contains '$name'.";
    }
}
