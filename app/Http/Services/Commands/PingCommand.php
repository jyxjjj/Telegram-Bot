<?php

namespace App\Http\Services\Commands;

use App\Http\Services\BaseCommand;
use App\Jobs\DeleteMessageWithKeyJob;
use App\Jobs\EditMessageTextWithKeyJob;
use App\Jobs\SendMessageWithKeyJob;
use Carbon\Carbon;
use DESMG\UUID;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class PingCommand extends BaseCommand
{
    public string $name = 'ping';
    public string $description = 'Show the latency to the bot server';
    public string $usage = '/ping';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $key = UUID::generateUniqueID();
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
            'text' => 'Calculating...',
        ];
        $this->dispatch(new SendMessageWithKeyJob($data, $key, null));
        $data['text'] = '';
        $sendTime = $message->getDate();
        $sendTime = Carbon::createFromTimestamp($sendTime)->getTimestampMs();
        $startTime = Cache::get("TelegramUpdateStartTime_$updateId");
        $startTime = Carbon::createFromTimestampMs($startTime)->getTimestampMs();
        $server_latency = $startTime - $sendTime;
        $endTime = Carbon::now()->getTimestampMs();
        $message_latency = $endTime - $startTime;
        $data['text'] .= "*Send Time:* `$sendTime`\n";
        $data['text'] .= "*Start Time:* `$startTime`\n";
        $data['text'] .= "*End Time:* `$endTime`\n";
        $data['text'] .= "*Server Latency:* `$server_latency` ms\n";
        $data['text'] .= "*Message Latency:* `$message_latency` ms\n";
        $IPs = [
            '149.154.175.53',
            '149.154.167.51',
            '149.154.175.100',
            '149.154.167.91',
            '91.108.56.130',
        ];
        for ($i = 1; $i <= count($IPs); $i++) {
            $IP = $IPs[$i - 1];
            $ping = $this->ping($IP);
            $data['text'] .= "*DC$i($IP) Latency:* `$ping ms`\n";
        }
        $data['text'] = substr($data['text'], 0, -1);
        $this->dispatch(new EditMessageTextWithKeyJob($data, $key));
        $this->dispatch(new DeleteMessageWithKeyJob($data, $key));
    }

    /**
     * @param $host
     * @return int
     */
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