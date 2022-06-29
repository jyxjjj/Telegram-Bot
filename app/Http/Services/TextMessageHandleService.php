<?php

namespace App\Http\Services;

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class TextMessageHandleService extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public static function handle(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        $messageType = $message->getType();
        $from = $message->getFrom();
//        $serverResponse = Request::getChatAdministrators([
//            'chat_id' => $message->getChat()->getId(),
//        ]);
//        /** @var ChatMember[] $chatMembers */
//        $chatMembers = $serverResponse->getResult();

    }
}
