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
        foreach ($pendingData as $cvid => $user_id) {
            $userData = Conversation::get($user_id, 'contribute');
            $cvinfo = $userData[$cvid];
            $name = $cvinfo['name'];
            $link = $cvinfo['link'];
            $data['text'] .= "投稿ID: <code>$cvid</code>\n用户ID: <code>$user_id</code>\n投稿名称: <code>$name</code>\n投稿链接: <code>$link</code>\n\n";
        }
        $data['text'] .= '请根据信息搜索聊天记录';
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
