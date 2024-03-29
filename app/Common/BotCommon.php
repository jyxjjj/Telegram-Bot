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

namespace App\Common;

use GuzzleHttp\Client;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class BotCommon
{
    /**
     * @var ?Telegram
     */
    public static ?Telegram $telegram = null;

    /**
     * @param Message $message
     * @param ?Telegram $telegram
     * @return bool
     * @throws TelegramException
     */
    public static function isAdmin(Message $message, ?Telegram $telegram = null): bool
    {
        if ($telegram) {
            return $telegram->isAdmin($message->getFrom()->getId());
        }
        return self::getTelegram()->isAdmin($message->getFrom()->getId());
    }

    /**
     * @param int $timeout
     * @return Telegram
     * @throws TelegramException
     */
    public static function getTelegram(int $timeout = 60): Telegram
    {
        if (self::$telegram) {
            return self::$telegram;
        }
        self::$telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'), env('TELEGRAM_BOT_USERNAME'));
        self::$telegram->enableAdmin(env('TELEGRAM_ADMIN_USER_ID'));
        self::$telegram->setDownloadPath(storage_path('app/telegram'));
        self::$telegram->setUploadPath(storage_path('app/telegram'));
        Request::setClient(self::getClient($timeout));
        return self::$telegram;
    }

    /**
     * @param int $timeout
     * @return Client
     */
    private static function getClient(int $timeout = 60): Client
    {
        return new Client([
            'base_uri' => env('TELEGRAM_API_BASE_URI'),
            'proxy' => env('TELEGRAM_PROXY'),
            'timeout' => $timeout,
        ]);
    }
}
