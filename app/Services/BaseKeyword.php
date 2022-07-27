<?php

namespace App\Services;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

abstract class BaseKeyword
{
    use DispatchesJobs;

    public string $name;
    public string $description;
    public string $pattern;
    public string $version = '1.0.0';
    public bool $ignoreAdmin = false;
    public bool $ignorePrivate = false;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public abstract function execute(Message $message, Telegram $telegram, int $updateId): void;
}
