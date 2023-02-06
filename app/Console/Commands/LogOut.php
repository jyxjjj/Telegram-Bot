<?php

namespace App\Console\Commands;

use App\Common\BotCommon;
use Illuminate\Console\Command;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class LogOut extends Command
{
    protected $signature = 'command:LogOut';
    protected $description = 'Log Out https://core.telegram.org/bots/api#logout';

    /**
     * @return int
     */
    public function handle(): int
    {
        try {
            BotCommon::getTelegram();
            $result = Request::logOut();
            self::info($result->getDescription());
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }
}
