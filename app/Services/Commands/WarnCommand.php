<?php

namespace App\Services\Commands;

use App\Jobs\BanMemberByWarnJob;
use App\Jobs\DeleteMessageJob;
use App\Jobs\SendMessageJob;
use App\Models\TChatWarns;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class WarnCommand extends BaseCommand
{
    public string $name = 'warn';
    public string $description = 'Warn a user';
    public string $usage = '/warn';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $param = $message->getText(true);
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

        if ($param == '' || $param == null) {
            $userId = null;
            $replyToMessage = $message->getReplyToMessage();
            if ($replyToMessage != null) {
                $data['text'] .= "Get user id from reply message.\n";
                $userId = $replyToMessage->getFrom()->getId();
                $deleter = [
                    'chat_id' => $chatId,
                    'message_id' => $replyToMessage->getMessageId(),
                ];
                $this->dispatch(new DeleteMessageJob($deleter, 0));
            }
        } else {
            /** @noinspection DuplicatedCode */
            if (str_starts_with($param, '@')) {
                $data['text'] .= "Get user id via resolve the username you inputed.\n";
                $userId = null;
            } else if (is_numeric($param)) {
                $data['text'] .= "Get user id you inputed.\n";
                $userId = $param;
            } else {
                $userId = null;
                $entities = $message->getEntities();
                foreach ($entities as $entity) {
                    if ($entity->getType() == 'text_mention') {
                        $data['text'] .= "Get user id via resolve the user you mentioned.\n";
                        $userId = $entity->getUser()->getId();
                    }
                }
            }
        }
        if (!is_numeric($userId)) {
            $data['text'] .= "Invalid user id.\n";
            $data['text'] .= "*Usage:* Reply to his message with /warn.\n";
            $data['text'] .= "*Usage:* /warn + @username.\n";
            $data['text'] .= "*Usage:* /warn user_id.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        TChatWarns::addUserWarn($chatId, $userId);
        $warns = TChatWarns::getUserWarns($chatId, $userId);
        if ($warns >= 3) {
            $data['text'] .= "*Warning:* This user [$userId](tg://user?id=$userId) has been warned 3 times.\n";
            $data['text'] .= "*Warning:* Banning user [$userId](tg://user?id=$userId).\n";
            $this->dispatch(new SendMessageJob($data));
            $data = [
                'chatId' => $chatId,
                'messageId' => $messageId,
                'banUserId' => $userId,
            ];
            $this->dispatch(new BanMemberByWarnJob($data));
        } else {
            $data['text'] .= "Warning user [$userId](tg://user?id=$userId).\n";
            $data['text'] .= "*Current warn times:* $warns.\n";
            $this->dispatch(new SendMessageJob($data));
        }
    }
}
