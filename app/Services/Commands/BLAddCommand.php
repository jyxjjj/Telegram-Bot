<?php

namespace App\Services\Commands;

use App\Common\Log\BL;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BLAddCommand extends BaseCommand
{
    public string $name = 'bladd';
    public string $description = 'Add Blacklist';
    public string $usage = '/bladd {用户ID}';
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
        $data['text'] = BL::add($userId) ? '黑名单添加成功' : '黑名单添加失败';
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
