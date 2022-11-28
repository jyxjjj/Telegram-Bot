<?php

namespace App\Services\Commands;

use App\Common\Log\WL;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class WLRemoveCommand extends BaseCommand
{
    public string $name = 'wlremove';
    public string $description = 'Remove Whitelist';
    public string $usage = '/wlremove {用户ID}';
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
        $param = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $userId = $param;
        if (!is_numeric($userId)) {
            $data['text'] .= '用户ID输入错误';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data['text'] = WL::remove($userId) ? '白名单添加成功' : '白名单添加失败';
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
