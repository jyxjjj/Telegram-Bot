<?php

namespace App\Services\Commands;

use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ClearWarnCommand extends BaseCommand
{
    public string $name = 'clearwarn';
    public string $description = 'Clear the warn times of a user';
    public string $usage = '/clearwarn';

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
