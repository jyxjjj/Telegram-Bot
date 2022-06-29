<?php

namespace App\Http\Services;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

abstract class BaseCommand
{
    use DispatchesJobs;

    public string $name;
    public string $description;
    public string $usage;
    public string $version = '1.0.0';
    public bool $admin = false;
    public bool $private = false;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public abstract function execute(Message $message, Telegram $telegram, int $updateId): void;
}
