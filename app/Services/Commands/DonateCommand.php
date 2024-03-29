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

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class DonateCommand extends BaseCommand
{
    public string $name = 'donate';
    public string $description = 'Donate Infomation';
    public string $usage = '/donate';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= "\n✥  ✥  ✥  <b>Donate Infomation</b>  ✥  ✥  ✥";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  Thank you for donate this project";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['reply_markup'] = new InlineKeyboard([]);
        $button = new InlineKeyboardButton([
            'text' => 'Donate',
            'url' => 'https://www.desmg.com/donate',
        ]);
        $data['reply_markup']->addRow($button);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
