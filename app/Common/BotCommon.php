<?php

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
