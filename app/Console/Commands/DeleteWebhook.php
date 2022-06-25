<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class DeleteWebhook extends Command
{
    protected $signature = 'command:DeleteWebhook';
    protected $description = 'Delete Webhook https://core.telegram.org/bots/api#deletewebhook';

    public function handle(): int
    {
        try {
            $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'), env('TELEGRAM_BOT_USERNAME'));
            $result = $telegram->deleteWebhook(['drop_pending_updates' => true]);
            self::info($result->getDescription());
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
