<?php

namespace App\Services;

use App\Jobs\DeleteMessageJob;
use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AutoDeleteHandler extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return bool
     */
    public function handle(Message $message, Telegram $telegram, int $updateId): bool
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $senderId = $message->getFrom()->getId();
        if (in_array($senderId, [5325743711,])) {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ];
            $this->dispatch(new DeleteMessageJob($data, 0));
            return true;
        }
        return false;
    }
}
