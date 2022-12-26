<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Models\TChatAdmins;
use App\Models\TChatWarns;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ClearWarnCommand extends BaseCommand
{
    public string $name = 'clearwarn';
    public string $description = 'Clear the warn times of a user';
    public string $usage = '/clearwarn [reply_to] [at(unsupported)|text_mention|user_id]';

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
        unset($userId);

        if ($param == '' || $param == null) {
            $userId = null;
            $replyTo = $message->getReplyToMessage();
            if ($replyTo != null) {
                $userId = $replyTo->getFrom()->getId();
                unset($admins);
                $data['text'] .= "Get user id from reply message.\n";
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
            if (!is_numeric($userId)) {
                $data['text'] .= "Invalid user id.\n";
                $data['text'] .= "<b>Usage</b>: Reply to his message with /unwarn.\n";
                $data['text'] .= "<b>Usage</b>: /unwarn @username.\n";
                $data['text'] .= "<b>Usage</b>: /unwarn user_id.\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
        }
        $warns = TChatWarns::getUserWarns($chatId, $userId);
        if ($warns <= 0) {
            $data['text'] .= "<b>Error</b>: This user has no warns.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        TChatWarns::clearUserWarn($chatId, $userId);
        $data['text'] .= "Removed all warnings of user <a href='tg://user?id=$userId'>$userId</a>.\n";
        $this->dispatch(new SendMessageJob($data));
    }
}