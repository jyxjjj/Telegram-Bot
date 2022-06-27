<?php

namespace App\Http\Services;

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class CommandHandleService extends BaseService
{
    protected array $command_object = [];

    public static function handle(Message $message, Telegram $telegram, int $updateId)
    {
        $this->command_object = $telegram->getCommandsList();
    }
}
