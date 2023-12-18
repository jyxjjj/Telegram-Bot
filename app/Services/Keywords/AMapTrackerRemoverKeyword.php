<?php

namespace App\Services\Keywords;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseKeyword;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AMapTrackerRemoverKeyword extends BaseKeyword
{
    public string $name = 'AMap tracker remover';
    public string $description = 'Remove AMap tracker from surl link';
    public string $version = '1.0.2';
    protected string $pattern = '/(surl\.amap\.com)/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $text = $message->getText();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $this->handle($text, $data);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function handle(string $text, array &$data): void
    {
        $pattern = '/(http(s)?:\/\/)?(surl\.amap\.com)\/?[a-zA-Z\d]+/';
        if (preg_match_all($pattern, $text, $matches)) {
            $pattern = '/https:\/\/(www|wb)\.amap\.com\/\?p=([a-zA-Z\d]+),(\d+\.\d+),(\d+\.\d+)/';
            $data['text'] .= "AMap Tracker Removed\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (count($matches[0]) > 3) {
                $count = 3;
            } else {
                $count = count($matches[0]);
            }
            for ($i = 0; $i < $count; $i++) {
                $link = $matches[0][$i];
                $this->normalizeLink($link);
                $location = $this->getLocation($link);
                $location = urldecode($location);
                if (preg_match($pattern, $location, $matchedLocation)) {
                    $location = "https://www.amap.com/place/$matchedLocation[2]";
                    $data['text'] .= "<b>Link:</b> <code>$location</code>\n";
                    $button = new InlineKeyboardButton([
                        'text' => $location,
                        'url' => $location,
                    ]);
                    $data['reply_markup']->addRow($button);
                }
            }
        }
    }

    private function normalizeLink(string &$link): void
    {
        if (str_starts_with($link, 'http://')) {
            str_replace('http://', 'https://', $link);
        }
        if (str_starts_with($link, 'surl.amap.com')) {
            $link = "https://$link";
        }
    }

    private function getLocation(string $link): string
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-AMap-Link-Tracker-Remover/$this->version";
        return Http::
        connectTimeout(10)
            ->timeout(10)
            ->retry(3, 1000, throw: false)
            ->withHeaders($headers)
            ->withoutRedirecting()
            ->get($link)
            ->header('Location');
    }

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }
}
