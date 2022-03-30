<?php

namespace App\Http\Services\Bots;

use App\Http\Services\BaseService;
use Longman\TelegramBot\Entities\ChatMember\ChatMember;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class TextMessageHandleService extends BaseService
{
    public static function handle(Message $message, Telegram $telegram, int $updateId)
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