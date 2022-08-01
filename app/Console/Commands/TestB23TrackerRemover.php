<?php

namespace App\Console\Commands;

use App\Common\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestB23TrackerRemover extends Command
{
    protected $signature = 'command:testB23TrackerRemover';
    protected $description = 'Test B23 Tracker Remover';

    public function handle(): int
    {
        $text = <<<EOF
https://www.bilibili.com/video/av1234?p=1
https://www.bilibili.com/BV1234
www.bilibili.com/BV1234
bilibili.com/BV1234
https://b23.tv/BV1234
b23.tv/BV1234
EOF;
        $pattern = '/(http(s)?:\/\/)?(b23\.tv|(www\.)?bilibili\.com)\/(video\/)?[a-zA-Z\d]+(\?p=(\d){1,3})?/';
        if (preg_match_all($pattern, $text, $matches)) {
            $pattern = '/https:\/\/www.bilibili.com\/video\/[a-zA-Z\d]+(\?p=(\d){1,3})?/';
            for ($i = 0; $i < 3; $i++) {
                $link = $matches[0][$i];
                $this->normalizeLink($link);
                if (preg_match($pattern, $link)) {
                    self::info($link);
                } else {
                    $location = $this->getLocation($link);
                    if (preg_match($pattern, $location)) {
                        self::info($location);
                    }
                }
            }
        }
        return self::SUCCESS;
    }

    private function normalizeLink(string &$link)
    {
        if (str_starts_with($link, 'http://')) {
            str_replace('http://', 'https://', $link);
        }
        if (str_starts_with($link, 'b23.tv') || str_starts_with($link, 'bilibili.com') || str_starts_with($link, 'www.bilibili.com')) {
            $link = "https://$link";
        }
    }

    private function getLocation(string $link): string
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= "; Telegram-B23-Link-Tracker-Remover/0.1.0";
        return Http::
        withHeaders($headers)
            ->withoutRedirecting()
            ->get($link)
            ->header('Location');
    }
}
