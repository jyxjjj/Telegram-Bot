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
            $data['text'] .= "<b>Error</b>: This command is available only for channels.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        $subscribes = TBilibiliSubscribes::getAllSubscribeByChat($chatId);
        if (count($subscribes) > 0) {
            $data['text'] .= "<b>Subscribed UPs</b>:\n";
            foreach ($subscribes as $subscribe) {
                $data['text'] .= "<a href='https://space.bilibili.com/{$subscribe['mid']}'>{$subscribe['mid']}</a>\n";
            }
        } else {
            $data['text'] .= "<b>Error</b>: This chat did not subscribe anything.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }
}
