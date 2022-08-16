<?php

namespace App\Services\Commands;

use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class WarnCommand extends BaseCommand
{
    public string $name = 'warn';
    public string $description = 'Warn a user';
    public string $usage = '/warn';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
    }
}
