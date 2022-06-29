<?php

namespace App\Console\Commands;

use App\Common\BotCommon;
use Illuminate\Console\Command;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class DeleteWebhook extends Command
{
    protected $signature = 'command:DeleteWebhook';
    protected $description = 'Delete Webhook https://core.telegram.org/bots/api#deletewebhook';

    /**
     * @return int
     */
    public function handle(): int
    {
        try {
            BotCommon::getTelegram();
            $result = Request::deleteWebhook(['drop_pending_updates' => true]);
            self::info($result->getDescription());
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
