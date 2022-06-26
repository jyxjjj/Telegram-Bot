<?php

namespace App\Common;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class Client
{
    private static function getClient(): \GuzzleHttp\Client
    {
        return new \GuzzleHttp\Client([
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
        Request::setClient(self::getClient());
        return $telegram;
    }
}
