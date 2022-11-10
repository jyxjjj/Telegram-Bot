<?php

namespace App\Console\Commands;

use App\Common\BotCommon;
use Illuminate\Console\Command;
use Longman\TelegramBot\Request;

class Test extends Command
{
    protected $signature = 'test';
    protected $description = 'Test';

    public function handle(): int
    {
        $telegram = BotCommon::getTelegram();
        $data = [
            'chat_id' => env('YPP_SOURCE_ID'),
            'text' => '删除按钮',
            'reply_markup' => ['remove_keyboard' => true],
        ];
        $result = Request::sendMessage($data);
        return self::SUCCESS;
    }
}
