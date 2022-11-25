<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Models\TBilibiliSubscribes;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BilibiliUnSubscribeCommand extends BaseCommand
{
    public string $name = 'bilibiliunsubscribe';
    public string $description = 'unsubscribe bilibili videos of an UP';
    public string $usage = '/bilibiliunsubscribe';

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
        $mid = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        //#region Detect Chat Type
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data['text'] .= "<b>Error</b>: This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Detect Admin Rights
        $admins = TChatAdmins::getChatAdmins($chatId);
        $userId = $message->getFrom()->getId();
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "<b>Error</b>: You should be an admin of this chat to use this command.\n\n";
            $data['text'] .= "<b>Warning</b>: This command can be used by people who was an admin before update admin list.\n\n";
            $data['text'] .= "<b>Notice</b>: Send /updatechatadministrators to update chat admin list.\n\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Check params
        if (!is_numeric($mid)) {
            $data['text'] .= "Invalid mid.\n";
            $data['text'] .= "<b>Usage</b>: /bilibilisubscribe mid.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        if (TBilibiliSubscribes::removeSubscribe($chatId, $mid) > 0) {
            $data['text'] .= "Unsubscribe successfully.\n";
        } else {
            $data['text'] .= "<b>Error</b>: Unsubscribe failed.\n";
            $data['text'] .= "One possibility is that this chat did not subscribe anything.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }
}
