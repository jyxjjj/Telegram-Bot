<?php

namespace App\Services\Base;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

abstract class BaseKeyword
{
    use DispatchesJobs;

    /**
     * @var string $name The name of the Handler
     */
    public string $name;

    /**
     * @var string $description Description of the Handler
     */
    public string $description;
    /**
     * @var string $version Version of the Handler
     */
    public string $version = '1.0.0';
    /**
     * @var bool $ignoreAdmin Ignore administrators
     */
    public bool $ignoreAdmin = false;
    /**
     * @var bool $ignorePrivate Ignore private messages
     */
    public bool $ignorePrivate = false;
    /**
     * @var string $pattern Pattern to match the message
     */
    protected string $pattern;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public abstract function execute(Message $message, Telegram $telegram, int $updateId): void;

    /**
     * @param Message $message
     * @return bool
     */
    public abstract function preExecute(Message $message): bool;
}
