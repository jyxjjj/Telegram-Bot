<?php

namespace App\Services\Commands;

use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class UnWarnCommand extends BaseCommand
{
    public string $name = 'unwarn';
    public string $description = 'reduce once warn times of a user';
    public string $usage = '/unwarn';

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
