<?php

namespace App\Http\Services;

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

abstract class BaseCommand
{
    public string $name;
    public string $description;
    public string $usage;
    public string $version = '1.0.0';
    public bool $admin = false;
    public bool $private = false;

    abstract function execute(Message $message, Telegram $telegram, int $updateId): void;
}
