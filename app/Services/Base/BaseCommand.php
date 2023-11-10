<?php

namespace App\Services\Base;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

abstract class BaseCommand
{
    use DispatchesJobs;

    /**
     * @var string $name The name of the command
     */
    public string $name;

    /**
     * @var string $description Description of the command
     */
    public string $description;

    /**
     * @var string $usage Example: /command [parameter]
     */
    public string $usage;

    /**
     * @var string $version Version of the command
     */
    public string $version = '1.0.0';

    /**
     * @var bool $admin Need admin permission to execute
     */
    public bool $admin = false;

    /**
     * @var bool $private Private messages only
     */
    public bool $private = false;

    /**
     * @param Message  $message
     * @param Telegram $telegram
     * @param int      $updateId
     * @return void
     */
    public abstract function execute(Message $message, Telegram $telegram, int $updateId): void;
}
