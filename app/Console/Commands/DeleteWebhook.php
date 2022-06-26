<?php

namespace App\Console\Commands;

use App\Common\Client;
use Illuminate\Console\Command;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class DeleteWebhook extends Command
{
    protected $signature = 'command:DeleteWebhook';
    protected $description = 'Delete Webhook https://core.telegram.org/bots/api#deletewebhook';

    public function handle(): int
    {
        try {
            Client::getTelegram();
            $result = Request::deleteWebhook(['drop_pending_updates' => true]);
            self::info($result->getDescription());
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
