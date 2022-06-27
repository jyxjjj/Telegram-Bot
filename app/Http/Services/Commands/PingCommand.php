<?php

namespace App\Http\Services\Commands;

use App\Common\BotCommon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
        $sendTime = $message->getDate();
        $sendTime = Carbon::createFromTimestamp($sendTime)->getTimestampMs();
        $startTime = Cache::get("TelegramUpdateStartTime_$updateId");
        $startTime = Carbon::createFromTimestampMs($startTime)->getTimestampMs();
        $endTime = Carbon::now()->getTimestampMs();
        $server_latency = $startTime - $sendTime;
        $message_latency = $endTime - $startTime;
        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text' => "Send time: $sendTime\nStart time: $startTime\nEnd time: $endTime\nServer latency: $server_latency ms\nMessage latency: $message_latency ms",
        ];
        BotCommon::getTelegram();
        Request::sendMessage($data);
    }
}
