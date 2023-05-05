<?php

namespace App\Services\Commands;

use App\Jobs\DeleteMessageJob;
use App\Jobs\SendMessageJob;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class DeleteCommand extends BaseCommand
{
    public string $name = 'delete';
    public string $description = /** @lang text */
        'Delete User\'s Message from Chat';
    public string $usage = '/delete [reply_to]';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];

        $admins = TChatAdmins::getChatAdmins($chatId);

        $userId = $message->getFrom()->getId();
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "<b>Error</b>: You should be an admin of this chat to use this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $replyTo = $message->getReplyToMessage();
        if (!$replyTo) {
            $data['text'] .= "<b>Error</b>: You should reply to a message for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $deleter = [
            'chat_id' => $chatId,
            'message_id' => $replyTo->getMessageId(),
        ];
        $this->dispatch(new DeleteMessageJob($deleter, 0));
    }
}
