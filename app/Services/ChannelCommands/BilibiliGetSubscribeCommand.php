<?php

namespace App\Services\ChannelCommands;

use App\Jobs\SendMessageJob;
use App\Models\TBilibiliSubscribes;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BilibiliGetSubscribeCommand extends BaseCommand
{
    public string $name = 'bilibiligetsubscribe';
    public string $description = 'get all subscribed bilibili videos of an UP in this chat';
    public string $usage = '/bilibiligetsubscribe';

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
        $subscribes = TBilibiliSubscribes::getAllSubscribeByChat($chatId);
        if (count($subscribes) > 0) {
            $data['text'] .= "*Subscribed UPs:*\n";
            foreach ($subscribes as $subscribe) {
                $data['text'] .= "[{$subscribe['mid']}](https://space.bilibili.com/{$subscribe['mid']})\n";
            }
        } else {
            $data['text'] .= "*Error:* This chat did not subscribe anything.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }
}
