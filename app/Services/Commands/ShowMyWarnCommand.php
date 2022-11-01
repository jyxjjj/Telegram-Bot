<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Models\TChatWarns;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ShowMyWarnCommand extends BaseCommand
{
    public string $name = 'showmywarn';
    public string $description = 'Show the warn times of a user';
    public string $usage = '/showmywarn [reply_to] [at(unsupported)|text_mention|user_id]';

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
            $replyToMessage = $message->getReplyToMessage();
            if ($replyToMessage == null) {
                $data['text'] .= "Get user id of yourself.\n";
                $userId = $message->getFrom()->getId();
            } else {
                $data['text'] .= "Get user id from reply message.\n";
                $userId = $replyToMessage->getFrom()->getId();
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
            $data['text'] .= "*Usage:* /showmywarn to show your own warn times.\n";
            $data['text'] .= "*Usage:* Reply to his message with /showmywarn.\n";
            $data['text'] .= "*Usage:* /showmywarn @username.\n";
            $data['text'] .= "*Usage:* /showmywarn user\_id.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $warns = TChatWarns::getUserWarns($chatId, $userId);
        if ($warns > 0) {
            $data['text'] .= "This user [$userId](tg://user?id=$userId) has been warned for $warns times.\n";
        } else {
            $data['text'] .= "This user [$userId](tg://user?id=$userId) had never been warned.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }
}
