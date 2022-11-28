<?php

namespace App\Services\Commands;

use App\Common\Log\WL;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class LWLCommand extends BaseCommand
{
    public string $name = 'lwl';
    public string $description = 'Get Whitelists';
    public string $usage = '/lwl';
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
            'text' => "白名单:\n",
        ];
        $data['text'] .= implode("\n", WL::getAll());
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
