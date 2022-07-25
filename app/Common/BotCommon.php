<?php

namespace App\Common;

use GuzzleHttp\Client;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ReplyToMessage;
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
     * @param Message|ReplyToMessage $message
     * @return string
     */
    public static function getSenderName(Message|ReplyToMessage $message): string
    {
        return $message->getChat()->getFirstName() . $message->getChat()->getLastName();
    }

    /**
     * @param Message $message
     * @return string
     */
    public static function getChatId(Message $message): string
    {
        return $message->getChat()->getId();
    }

    /**
     * @param Message $message
     * @return string
     */
    public static function getChatName(Message $message): string
    {
        return $message->getChat()->getTitle();
    }

    /**
     * @param Message $message
     * @return string
     */
    public static function getChatType(Message $message): string
    {
        return $message->getChat()->getType();
    }

    /**
     * @param Message $message
     * @return string
     */
    public static function getChatUsername(Message $message): string
    {
        return $message->getChat()->getUsername();
    }

    /**
     * @param Message|ReplyToMessage $message
     * @return string
     */
    public static function getMessageId(Message|ReplyToMessage $message): string
    {
        return $message->getMessageId();
    }

    /**
     * @param Message $message
     * @param ?Telegram $telegram
     * @return bool
     * @throws TelegramException
     */
    public static function isAdmin(Message $message, ?Telegram $telegram = null): bool
    {
        if ($telegram) {
            return $telegram->isAdmin(self::getSender($message));
        }
        return self::getTelegram()->isAdmin(self::getSender($message));
    }

    /**
     * @param Message|ReplyToMessage $message
     * @return int
     */
    public static function getSender(Message|ReplyToMessage $message): int
    {
        return $message->getFrom()->getId();
    }

    /**
     * @return Telegram
     * @throws TelegramException
     */
    public static function getTelegram(): Telegram
    {
        if (self::$telegram) {
            return self::$telegram;
        }
        self::$telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'), env('TELEGRAM_BOT_USERNAME'));
        self::$telegram->enableAdmin(env('TELEGRAM_ADMIN_USER_ID'));
        self::$telegram->setDownloadPath(storage_path('app/telegram'));
        self::$telegram->setUploadPath(storage_path('app/telegram'));
        Request::setClient(self::getClient());
        return self::$telegram;
    }

    /**
     * @return Client
     */
    private static function getClient(): Client
    {
        return new Client([
            'base_uri' => env('TELEGRAM_API_BASE_URI'),
            'proxy' => env('TELEGRAM_PROXY'),
            'timeout' => 60,
        ]);
    }
}
