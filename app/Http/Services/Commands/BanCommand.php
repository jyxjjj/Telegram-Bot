<?php

namespace App\Http\Services\Commands;

use App\Common\BotCommon;
use App\Http\Models\TChatAdmins;
use App\Http\Services\BaseCommand;
use App\Jobs\BanMemberJob;
use App\Jobs\SendMessageJob;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BanCommand extends BaseCommand
{
    public string $name = 'ban';
    public string $description = 'Ban User from Chat';
    public string $usage = '/ban';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $messageId = BotCommon::getMessageId($message);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];

        $replyTo = $message->getReplyToMessage();
        if (!$replyTo) {
            $data['text'] .= "*Error:* You should reply to a message for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $admins = TChatAdmins::getChatAdmins($chatId);

        $userId = BotCommon::getSender($message);
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "*Error:* You should be an admin of this chat to use this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $banUserId = BotCommon::getSender($replyTo);
        if (in_array($banUserId, $admins, true)) {
            $data['text'] .= "*Error:* You can't ban an admin.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $replyToMessageId = BotCommon::getMessageId($replyTo);

        $data = [
            'chatId' => $chatId,
            'messageId' => $messageId,
            'replyToMessageId' => $replyToMessageId,
            'userId' => $userId,
            'banUserId' => $banUserId,
        ];
        $this->dispatch(new BanMemberJob($data));
    }
}
