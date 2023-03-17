<?php

namespace App\Services\Commands;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ListCommand extends BaseCommand
{
    public string $name = 'list';
    public string $description = 'List Pending Contributions';
    public string $usage = '/list';
    public bool $private = false;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        if ($chatId != env('YPP_SOURCE_ID')) {
            return;
        }
        $data = [
            'chat_id' => $chatId,
            'text' => "待审核投稿:\n",
        ];
        $pendingData = Conversation::get('pending', 'pending');
        $pendingMessageLink = Conversation::get('messagelink', 'pending');
        $chatIdLink = str_replace('-100', '', $chatId);
        foreach ($pendingData as $cvid => $user_id) {
            $messageId = $pendingMessageLink[$cvid];
            $data['text'] .= "<a href=\"https://t.me/c/$chatIdLink/$messageId\">$cvid</a>\n";
        }
        if (strlen($data['text']) < 8) {
            $data['text'] .= "暂无待审核投稿\n";
        }
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
