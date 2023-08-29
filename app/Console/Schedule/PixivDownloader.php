<?php

namespace App\Console\Schedule;

use App\Common\Config;
use App\Exceptions\Handler;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PixivDownloader extends Command
{
    protected $signature = 'pixiv:download';
    protected $description = 'Download Daily Rank of Pixiv';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            [$data, $date] = $this->getRanks();
            self::info("Last Update: $date");
            $data = $this->buildData($data);
            $this->saveData($date, $data);
            return self::SUCCESS;
        } catch (Throwable $e) {
            self::error("Error({$e->getCode()}):{$e->getMessage()}@{$e->getFile()}:{$e->getLine()}");
            Handler::logError($e);
            return self::FAILURE;
        }
    }

    private function getRanks(): array
    {
        $headers = Config::CURL_HEADERS;
        $headers['Referer'] = 'https://www.pixiv.net/ranking.php?mode=daily';
        $data = [];
        $json['next'] = 1;
        $date = Carbon::createFromFormat('Ymd', '19700101');
        while ($json['next']) {
            self::info("Getting Page {$json['next']}");
            $url = "https://www.pixiv.net/ranking.php?mode=daily&content=illust&p={$json['next']}&format=json";
            $response = Http::withHeaders($headers)
                ->connectTimeout(10)
                ->timeout(10)
                ->retry(3, 1000, throw: false)
                ->get($url);
            $json = $response->json();
            $code = $response->status();
            if (!isset($json['contents'])) {
                self::error("Pixiv API Error: $code");
                break;
            }
            $data = array_merge($data, $json['contents']);
            $date = Carbon::createFromFormat('Ymd', $json['date']);
            sleep(1);
        }
        return [$data, $date];
    }

    private function buildData(array $data): array
    {
        $result = [];
        foreach ($data as $item) {
            $replace = preg_replace('/c\/\d+x\d+\//', '', $item['url']);
            $url = $replace ?? $item['url'];
            $artwork_id = $item['illust_id'];
            $title = $item['title'];
            $author = $item['user_name'];
            $author_id = $item['user_id'];
            $artwork_url = "https://www.pixiv.net/artworks/$artwork_id";
            $author_url = "https://www.pixiv.net/users/$author_id";
            $result[] = [
                'artwork_id' => $artwork_id,
                'title' => $title,
                'author' => $author,
                'author_id' => $author_id,
                'artwork_url' => $artwork_url,
                'author_url' => $author_url,
                'url' => $url,
            ];
        }
        return $result;
    }

    private function saveData(Carbon $date, array $data): void
    {
        $storage = Storage::disk('public');
        $path = "pixiv/{$date->format('Y-m-d')}.json";
        $data = [
            'date' => $date->format('Y-m-d H:i:s'),
            'data' => $data,
        ];
        $data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $storage->put($path, $data);
        self::info("Saved to {$storage->path($path)}");
    }
}
