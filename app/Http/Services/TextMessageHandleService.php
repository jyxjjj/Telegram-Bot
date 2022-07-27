<?php

namespace App\Http\Services;

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class TextMessageHandleService extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     */
    public static function handle(Message $message, Telegram $telegram, int $updateId): void
    {
        KeywordHandleService::handle($message, $telegram, $updateId);
    }
}
