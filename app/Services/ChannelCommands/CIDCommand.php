<?php

namespace App\Services\ChannelCommands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class CIDCommand extends BaseCommand
{
    public string $name = 'cid';
    public string $description = 'Chat ID';
    public string $usage = '/cid';
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
        $data = [
            'chat_id' => $chatId,
            'text' => $chatId,
        ];
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
