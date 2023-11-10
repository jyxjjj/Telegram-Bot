<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\ChatMember\ChatMember;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Throwable;

class UpdateChatAdministratorsCommand extends BaseCommand
{
    public string $name = 'updatechatadministrators';
    public string $description = 'Update Chat Administrators';
    public string $usage = '/updatechatadministrators';

    /**
     * @param Message  $message
     * @param Telegram $telegram
     * @param int      $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $message->getMessageId(),
            'text' => '',
        ];
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data['text'] .= "<b>Error</b>: This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        $response = Request::getChatAdministrators([
            'chat_id' => $chatId,
        ]);
        /** @var ChatMember[] $admins */
        $admins = $response->getResult();
        try {
            TChatAdmins::clearAdmin($chatId);
            $i = 0;
            foreach ($admins as $admin) {
                $i++;
                TChatAdmins::addAdmin($chatId, $admin->getUser()->getId());
            }
            $data['text'] .= "Updated chat administrators successfully.\n";
            $data['text'] .= "<b>This group is a</b> <code>$chatType</code>.\n";
            $data['text'] .= "<b>There are</b> <code>$i</code> admins in this group.\n";
        } catch (Throwable $e) {
            $data['text'] .= "<b>Error({$e->getCode()})</b>: database error.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }
}
