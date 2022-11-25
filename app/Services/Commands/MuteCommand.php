<?php

namespace App\Services\Commands;

use App\Jobs\RestrictMemberJob;
use App\Jobs\SendMessageJob;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class MuteCommand extends BaseCommand
{
    public string $name = 'mute';
    public string $description = 'Mute User of a Chat';
    public string $usage = '/mute [reply_to] {time}[s|m|h|d]';

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
        $param = $message->getText(true);
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

        $restrictUserId = $replyTo->getFrom()->getId();
        if (in_array($restrictUserId, $admins, true)) {
            $data['text'] .= "<b>Error</b>: You can't mute an admin.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $time = trim($param);

        if ($time == '') {
            $time = 3600;
        } else {
            if (!is_numeric($time)) {
                $time = substr($time, 0, -1);
                if (!is_numeric($time)) {
                    $data['text'] .= "<b>Error</b>: Time should be a number in seconds or in units of \"s,m,h,d\".\n";
                    $this->dispatch(new SendMessageJob($data));
                    return;
                }
                $unit = strtolower(substr($time, -1));
                $time = match ($unit) {
                    'm' => $time * 60,
                    'h' => $time * 3600,
                    'd' => $time * 86400,
                    default => $time,
                };
            }
            if ($time > 366 * 24 * 3600) {
                $data['text'] .= "<b>Error</b>: Time more than 366 days will forever mute user.\n\n";
                $data['text'] .= "<b>Info</b>: Bot now does not support automaticly unrestrict member.\n\n";
                $data['text'] .= "<b>Notice</b>: If you really want to mute user forever, use /kick instead.\n\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
            if ($time < 30) {
                $data['text'] .= "<b>Error</b>: Time less than 30 seconds will forever mute user.\n\n";
                $data['text'] .= "<b>Info</b>: Bot now does not support automaticly unrestrict member.\n\n";
                $data['text'] .= "<b>Notice</b>: If you really want to mute user forever, use /kick instead.\n\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
        }

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'user_id' => $restrictUserId,
        ];
        $this->dispatch(new RestrictMemberJob($data, $time));
    }
}
