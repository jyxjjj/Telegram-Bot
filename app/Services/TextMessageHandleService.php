<?php

namespace App\Services;

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
    public function handle(Message $message, Telegram $telegram, int $updateId): void
    {
        (new KeywordHandleService)->handle($message, $telegram, $updateId);
    }
}
