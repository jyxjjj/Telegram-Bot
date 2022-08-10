<?php

namespace App\Services\Commands;

use App\Common\BotCommon;
use App\Common\Config;
use App\Jobs\SendPhotoJob;
use App\Services\Base\BaseCommand;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class SpeedTestCommand extends BaseCommand
{
    public string $name = 'speedtest';
    public string $description = 'Show the speed of the server of the bot';
    public string $usage = '/speedtest';
    public bool $admin = true;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $messageId = BotCommon::getMessageId($message);
        $server = $this->getBestServer();
        $download = $this->download($server);
        $upload = $this->upload($server);
        $share = $this->share($download, $upload, $server);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'photo' => $share,
            'caption' => "*SpeedTest*\n",
        ];
        $data['caption'] .= "*Server:* `{$server['name']}`\n";
        $data['caption'] .= "*Sponsor:* `{$server['sponsor']}`\n";
        $data['caption'] .= "*Upload:* `$upload` Kbps\n";
        $data['caption'] .= "*Download:* `$download` Kbps\n";
        $data['caption'] .= "*Latency:* `{$server['latency']}`\n";
        $this->dispatch(new SendPhotoJob($data, 0));
    }

    private function getBestServer(): array
    {
        $servers = $this->getServers();
        foreach ($servers as &$server) {
            $random = openssl_random_pseudo_bytes(8);
            $random = bin2hex($random);
            $url = "{$server['protocol']}://{$server['host']}/speedtest/latency.txt?r=$random";
            $start = Carbon::now()->getTimestampMs();
            for ($i = 0; $i < 10; $i++) {
                try {
                    Http::withHeaders(Config::CURL_HEADERS)
                        ->timeout(1)
                        ->get($url);
                } catch (Exception) {

                }
            }
            $end = Carbon::now()->getTimestampMs();
            $latency = $end - $start;
            $server['latency'] = $latency / 10;
        }
        usort($servers, fn($a, $b) => $a['latency'] <=> $b['latency']);
        return $servers[0];
    }

    private function getServers(): array
    {
        $data = Http::
        withHeaders(Config::CURL_HEADERS)
            ->get('https://www.speedtest.net/speedtest-servers.php')
            ->body();
        if (strlen($data) < 10) {
            return [];
        }
        $data = (array)simplexml_load_string($data);
        $data = json_encode($data);
        $data = json_decode($data, true);
        $server = [];
        foreach ($data['servers']['server'] as $s) {
            $server[] = $s['@attributes'];
        }
        for ($i = 0; $i < count($server); $i++) {
            if (str_starts_with('https', $server[$i]['url'])) {
                $server[$i]['protocol'] = 'https';
            } else {
                $server[$i]['protocol'] = 'http';
            }
        }
        return $server;
    }

    private function download($server): float
    {
        $random = openssl_random_pseudo_bytes(16);
        $random = bin2hex($random);
        $url = "{$server['protocol']}://{$server['host']}/download?size=250000&r=0.$random";
        $start = Carbon::now()->getTimestampMs();
        for ($i = 0; $i < 20; $i++) {
            try {
                Http::withHeaders(Config::CURL_HEADERS)
                    ->get($url);
            } catch (Exception) {
                return -1;
            }
        }
        $end = Carbon::now()->getTimestampMs();
        $time = $end - $start;
        return number_format(5000000 / $time * 1000 / 8, 4, '.', '');
    }

    private function upload($server): float
    {
        $random = openssl_random_pseudo_bytes(16);
        $random = bin2hex($random);
        $url = "{$server['url']}?r=0.$random";
        $data = str_repeat('0', 50000);
        $start = Carbon::now()->getTimestampMs();
        for ($i = 0; $i < 20; $i++) {
            try {
                Http::withHeaders(Config::CURL_HEADERS)
                    ->withBody($data, 'image/jpeg')
                    ->post($url);
            } catch (Exception) {
                return -1;
            }
        }
        $end = Carbon::now()->getTimestampMs();
        $time = $end - $start;
        return number_format(1000000 / $time * 1000 / 8, 4, '.', '');
    }

    private function share($download, $upload, $server): string
    {
        $url = "https://www.speedtest.net/api/api.php";
        $headers = array_merge(Config::CURL_HEADERS, ['Referer' => 'https://c.speedtest.net/flash/speedtest.swf']);
        $hash = md5("{$server['latency']}-$upload-$download-297aae72");
        $data = [
            "recommendedserverid={$server['id']}",
            "ping={$server['latency']}",
            "download=$download",
            "upload=$upload",
            'screenresolution=',
            'promo=',
            'screendpi=',
            "testmethod={$server['protocol']}",
            "hash=$hash",
            'touchscreen=none',
            'startmode=pingselect',
            'accuracy=1',
            'bytesreceived=5000000',
            'bytessent=5000000',
            "serverid={$server['id']}",
        ];
        $data = implode('&', $data);
        $data = Http::withHeaders($headers)
            ->withBody($data, 'application/x-www-form-urlencoded')
            ->post($url)
            ->body();
        $data = explode('&', $data);
        foreach ($data as $dd) {
            $dd = explode('=', $dd);
            if ($dd[0] == 'resultid') {
                return "https://www.speedtest.net/result/$dd[1].png";
            }
        }
        return 'https://www.speedtest.net/result/1.png';
    }
}
