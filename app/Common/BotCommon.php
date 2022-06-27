<?php

namespace App\Common;

use GuzzleHttp\Client;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class BotCommon
{
    private static function getClient(): Client
    {
        return new Client([
            'base_uri' => env('TELEGRAM_API_BASE_URI'),
            'proxy' => env('TELEGRAM_PROXY'),
            'timeout' => 60,
        ]);
    }

    /**
     * @throws TelegramException
     */
    public static function getTelegram(): Telegram
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'), env('TELEGRAM_BOT_USERNAME'));
        $telegram->enableAdmin(env('TELEGRAM_ADMIN_USER_ID'));
        $telegram->setDownloadPath(storage_path('app/telegram'));
        $telegram->setUploadPath(storage_path('app/telegram'));
        Request::setClient(self::getClient());
        return $telegram;
    }
}
