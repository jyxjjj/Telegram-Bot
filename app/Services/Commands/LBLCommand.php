<?php

namespace App\Services\Commands;

use App\Common\Log\BL;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class LBLCommand extends BaseCommand
{
    public string $name = 'lbl';
    public string $description = 'Get Blacklists';
    public string $usage = '/lbl';
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
            'text' => "黑名单:\n",
        ];
        $data['text'] .= implode("\n", BL::getAll());
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
