<?php

namespace App\Services\ChannelCommands;

use App\Jobs\SendMessageJob;
use App\Models\TBilibiliSubscribes;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BilibiliClearSubscribeCommand extends BaseCommand
{
    public string $name = 'bilibiliclearsubscribe';
    public string $description = 'clear all subscribed bilibili videos of an UP in this chat';
    public string $usage = '/bilibiliclearsubscribe';

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
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        //#region Detect Chat Type
        $chatType = $message->getChat()->getType();
        if ($chatType !== 'channel') {
            $data['text'] .= "*Error:* This command is available only for channels.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        if (TBilibiliSubscribes::removeAllSubscribe($chatId) > 0) {
            $data['text'] .= "Unsubscribe all successfully.\n";
        } else {
            $data['text'] .= "*Error:* Unsubscribe failed.\n";
            $data['text'] .= "One possibility is that this chat did not subscribe anything.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }
}
