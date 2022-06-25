<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class GetWebhookInfo extends Command
{
    protected $signature = 'command:GetWebhookInfo';
    protected $description = 'Get Webhook Info https://core.telegram.org/bots/api#getwebhookinfo';

    public function handle(): int
    {
        try {
            new Telegram(env('TELEGRAM_BOT_TOKEN'), env('TELEGRAM_BOT_USERNAME'));
            $request = Request::getWebhookInfo();
            if (!$request->isOk()) {
                throw new TelegramException($request->getDescription());
            }
            self::info(json_encode($request->getResult(), JSON_PRETTY_PRINT));
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
