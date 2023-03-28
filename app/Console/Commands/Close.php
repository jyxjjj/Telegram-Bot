<?php

namespace App\Console\Commands;

use App\Common\BotCommon;
use Illuminate\Console\Command;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class Close extends Command
{
    protected $signature = 'command:Close';
    protected $description = 'Close https://core.telegram.org/bots/api#close';

    /**
     * @return int
     */
    public function handle(): int
    {
        try {
            BotCommon::getTelegram();
            $result = Request::close();
            self::info($result->getDescription());
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
