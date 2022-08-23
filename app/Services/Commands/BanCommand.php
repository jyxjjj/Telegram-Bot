<?php

namespace App\Services\Commands;

use App\Jobs\BanMemberJob;
use App\Jobs\SendMessageJob;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
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
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];

        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data['text'] .= "*Error:* This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $admins = TChatAdmins::getChatAdmins($chatId);

        $userId = $message->getFrom()->getId();
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "*Error:* You should be an admin of this chat to use this command.\n\n";
            $data['text'] .= "*Warning:* This command can be used by people who was an admin before update admin list.\n\n";
            $data['text'] .= "*Notice:* Send /updatechatadministrators to update chat admin list.\n\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $replyTo = $message->getReplyToMessage();
        if (!$replyTo) {
            $data['text'] .= "*Error:* You should reply to a message for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $banUserId = $replyTo->getFrom()->getId();
        if (in_array($banUserId, $admins, true)) {
            $data['text'] .= "*Error:* You can't ban an admin.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $replyToMessageId = $replyTo->getMessageId();

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