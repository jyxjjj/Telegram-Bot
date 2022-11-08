<?php

namespace App\Services\Base;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Telegram;

abstract class BaseCallback
{
    use DispatchesJobs;

    /**
     * @param CallbackQuery $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public abstract function handle(CallbackQuery $message, Telegram $telegram, int $updateId): void;
}
