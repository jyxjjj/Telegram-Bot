<?php

namespace App\Http\Services\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class PingCommand
{
    public string $name = 'ping';
    public string $description = 'Show the latency to the bot server';
    public string $usage = '/ping';
    public string $version = '1.0.0';
    public bool $admin = false;
    public bool $private = false;

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        Log::debug($message->getDate());
        $startTime = Carbon::createFromTimestamp($message->getDate());
        $endTime = Cache::get("TelegramUpdateStartTime_$updateId");
        $latency = $endTime - $startTime;
        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text' => "Pong! Latency: $latency ms",
        ];
        Request::sendMessage($data);
    }
}
