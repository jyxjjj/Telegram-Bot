<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ReplyToRejectKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return ($message->getChat()->isGroupChat() || $message->getChat()->isSuperGroup()) && $message->getChat()->getId() == env('YPP_SOURCE_ID');
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $data = Conversation::get($message->getChat()->getId(), 'contribute');

    }
}
