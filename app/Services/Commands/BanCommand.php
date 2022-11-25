<?php

namespace App\Services\Commands;

use App\Jobs\BanMemberJob;
use App\Jobs\DeleteMessageJob;
use App\Jobs\SendMessageJob;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BanCommand extends BaseCommand
{
    public string $name = 'ban';
    public string $description = 'Ban User from Chat';
    public string $usage = '/ban [reply_to]';

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
            $data['text'] .= "<b>Error</b>: This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $admins = TChatAdmins::getChatAdmins($chatId);

        $userId = $message->getFrom()->getId();
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "<b>Error</b>: You should be an admin of this chat to use this command.\n\n";
            $data['text'] .= "<b>Warning</b>: This command can be used by people who was an admin before update admin list.\n\n";
            $data['text'] .= "<b>Notice</b>: Send /updatechatadministrators to update chat admin list.\n\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $replyTo = $message->getReplyToMessage();
        if (!$replyTo) {
            $data['text'] .= "<b>Error</b>: You should reply to a message for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $banUserId = $replyTo->getFrom()->getId();
        if (in_array($banUserId, $admins, true)) {
            $data['text'] .= "<b>Error</b>: You can't ban an admin.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $deleter = [
            'chat_id' => $chatId,
            'message_id' => $replyTo->getMessageId(),
        ];
        $this->dispatch(new DeleteMessageJob($deleter, 0));

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'user_id' => $banUserId,
        ];
        $this->dispatch(new BanMemberJob($data));
    }
}
