<?php

namespace App\Services\Keywords;

use App\Services\Commands\DonateCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class DonateKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return $message->getChat()->isPrivateChat() && $message->getText() === '捐赠信息';
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        (new DonateCommand())->execute($message, $telegram, $updateId);
    }
}
