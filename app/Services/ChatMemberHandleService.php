<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;

class ChatMemberHandleService extends BaseService
{
    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
        $botId = $telegram->getBotId();
        $chatMember = $update->getChatMember();
        $chat = $chatMember->getChat();
        $chatId = $chat->getId();
        $from = $chatMember->getFrom();
        $fromId = $from->getId();
        $user = $chatMember->getNewChatMember()->getUser();
        $userId = $user->getId();
        $status = $chatMember->getNewChatMember()->getStatus();
        // status : [creator, administrator,] [member, restricted, left, kicked,]
        if ($fromId != $userId && $fromId != $botId) {
            return;
        }
        $log = <<<LOG

LOG;

    }
}
