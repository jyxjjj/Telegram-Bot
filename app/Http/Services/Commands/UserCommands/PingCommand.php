<?php

namespace App\Http\Services\Commands\UserCommands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class PingCommand
{
    protected string $name = 'ping';
    protected string $description = 'Show the latency to the bot server';
    protected string $usage = '/ping';
    protected string $version = '1.0.0';
    protected bool $private = false;

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $startTime = Carbon::createFromTimestamp($message->getDate());
        $endTime = Cache::get("TelegramUpdateStartTime_$updateId");
        $latency = $endTime - $startTime;
        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text' => "Pong! Latency: $latency ms",
        ];
        $serverResponse = Request::sendMessage($data);
        $description = $serverResponse->getDescription();
    }
}
