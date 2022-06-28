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
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
            'text' => '',
        ];
        $sendTime = $message->getDate();
        $sendTime = Carbon::createFromTimestamp($sendTime)->getTimestampMs();
        $startTime = Cache::get("TelegramUpdateStartTime_$updateId");
        $startTime = Carbon::createFromTimestampMs($startTime)->getTimestampMs();
        $server_latency = $startTime - $sendTime;
        $endTime = Carbon::now()->getTimestampMs();
        $message_latency = $endTime - $startTime;
        $data['text'] .= "Send time: `$sendTime`\n";
        $data['text'] .= "Start time: `$startTime`\n";
        $data['text'] .= "End time: `$endTime`\n";
        $data['text'] .= "Server latency: `$server_latency` ms\n";
        $data['text'] .= "Message latency: `$message_latency` ms\n";
        $IPs = [
            '149.154.175.53',
            '149.154.167.51',
            '149.154.175.100',
            '149.154.167.91',
            '91.108.56.130',
        ];
        for ($i = 1; $i <= count($IPs); $i++) {
            $ping = $this->ping($IPs[$i - 1]);
            $data['text'] .= "DC$i latency: `$ping ms`\n";
        }
        $data['text'] = substr($data['text'], 0, -1);
        BotCommon::getTelegram();
        Request::sendMessage($data);
    }

    private function ping($host): int
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $start = Carbon::now()->getTimestampMs();
        socket_connect($socket, $host, 80);
        $end = Carbon::now()->getTimestampMs();
        socket_close($socket);
        return $end - $start;
    }
}
