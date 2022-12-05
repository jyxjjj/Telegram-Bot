<?php

namespace App\Services\Commands;

use App\Jobs\EditMessageTextWithKeyJob;
use App\Jobs\SendMessageWithKeyJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Carbon;
use DESMG\RFC4122\UUID;
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
            'text' => 'Calculating...',
        ];
        $this->dispatch(new SendMessageWithKeyJob($data, $key, null));
        $data['text'] = '';
        $sendTime = $message->getDate();
        $sendTime = Carbon::createFromTimestamp($sendTime)->getTimestampMs();
        $startTime = Cache::get("TelegramUpdateStartTime_$updateId", 0);
        $startTime = Carbon::createFromTimestampMs($startTime)->getTimestampMs();
        $server_latency = $startTime - $sendTime;
        $telegramIP = Cache::get("TelegramIP_$updateId", '');
        $endTime = Carbon::now()->getTimestampMs();
        $message_latency = $endTime - $startTime;
        $data['text'] .= "<b>Send Time</b>: <code>$sendTime</code>\n";
        $data['text'] .= "<b>Start Time</b>: <code>$startTime</code>\n";
        $data['text'] .= "<b>End Time</b>: <code>$endTime</code>\n";
        $data['text'] .= "<b>Server Latency</b>: <code>$server_latency</code> ms\n";
        $data['text'] .= "<b>Message Latency</b>: <code>$message_latency</code> ms\n";
        $data['text'] .= "<b>Telegram Update IP</b>: <code>$telegramIP</code>\n";
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
            $data['text'] .= "<b>DC$i($IP) Latency</b>: <code>$ping ms</code>\n";
        }
        $this->dispatch(new EditMessageTextWithKeyJob($data, $key));
    }

    /**
     * @param $host
     * @return float
     */
    private function ping($host): float
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_SOCKET);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 1, 'usec' => 0]);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
        $start = Carbon::now()->getPreciseTimestamp();
        socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0);
        socket_recv($socket, $recv, 255, 0);
        socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0);
        socket_recv($socket, $recv, 255, 0);
        socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0);
        socket_recv($socket, $recv, 255, 0);
        socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0);
        socket_recv($socket, $recv, 255, 0);
        $end = Carbon::now()->getPreciseTimestamp();
        socket_close($socket);
        return ($end - $start) / 1000 / 4;
    }
}
