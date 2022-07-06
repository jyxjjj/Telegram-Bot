<?php

namespace App\Http\Services\Commands;

use App\Http\Models\TChatAdmins;
use App\Http\Services\BaseCommand;
use App\Jobs\SendMessageJob;
use Exception;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\ChatMember\ChatMember;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class UpdateChatAdministratorsCommand extends BaseCommand
{
    public string $name = 'updatechatadministrators';
    public string $description = 'Update Chat Administrators';
    public string $usage = '/updatechatadministrators';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
            'reply_to_message_id' => $message->getMessageId(),
            'text' => '',
        ];
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data['text'] .= "*Error:* This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        $response = Request::getChatAdministrators([
            'chat_id' => $chatId,
        ]);
        /** @var ChatMember[] $admins */
        $admins = $response->getResult();
        try {
            foreach ($admins as $admin) {
                TChatAdmins::addAdmin($chatId, $admin->getUser()->getId());
            }
            $data['text'] .= "Updated chat administrators successfully.\n";
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            $data['text'] .= "*Error({$e->getCode()}):* {$e->getFile()}:{$e->getLine()}\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }
}
