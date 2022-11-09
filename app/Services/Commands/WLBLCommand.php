<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class WLBLCommand extends BaseCommand
{
    public string $name = 'wlbl';
    public string $description = 'Set Blacklist and Whitelist';
    public string $usage = '/wlbl {白名单|黑名单|whitelist|blacklist|white|black|wl|bl|w|b} {用户ID}';
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
        $param = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        if ($chatId != env('YPP_SOURCE_ID')) {
            $data['text'] .= '';
        } else {
            $data['text'] .= '';
        }
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
