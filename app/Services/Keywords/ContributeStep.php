<?php

namespace App\Services\Keywords;

use App\Services\Base\BaseKeyword;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

abstract class ContributeStep extends BaseKeyword
{
    public string $name = '';
    public string $description = '';
    protected string $pattern = '//';

    abstract public function preExecute(Message $message): bool;

    abstract public function execute(Message $message, Telegram $telegram, int $updateId): void;
}
