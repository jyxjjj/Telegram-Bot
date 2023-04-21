<?php

namespace App\Services\Commands;

use App\Jobs\EditMessageTextWithKeyJob;
use App\Jobs\SendMessageWithKeyJob;
use App\Services\Base\BaseCommand;
use DESMG\RFC4122\UUID;
use DESMG\RFC792\Ping;
use Exception;
use Illuminate\Support\Carbon;
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
     * @throws Exception
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
        $ping = new Ping();
        for ($i = 1; $i <= count($IPs); $i++) {
            $IP = $IPs[$i - 1];
            $ping->setHost($IP);
            $ping->run();
            $latency = $ping->getLatency();
            $data['text'] .= "<b>DC$i($IP) Latency</b>: <code>$latency ms</code>\n";
        }
        unset($ping);
        $this->dispatch(new EditMessageTextWithKeyJob($data, $key));
    }
}
