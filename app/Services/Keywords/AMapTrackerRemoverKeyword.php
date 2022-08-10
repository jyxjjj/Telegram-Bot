<?php

namespace App\Services\Keywords;

use App\Common\BotCommon;
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
    protected string $pattern = '/(surl\.amap\.com)/';

    public function preExecute(string $text): bool
    {
        if (preg_match($this->pattern, $text)) {
            return true;
        }
        return false;
    }


    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $messageId = BotCommon::getMessageId($message);
        $text = BotCommon::getText($message);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $this->handle($text, $data);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function handle(string $text, array &$data)
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
                    $data['text'] .= "*Link:* `$location`\n";
                    $button = new InlineKeyboardButton([
                        'text' => $location,
                        'url' => $location,
                    ]);
                    $data['reply_markup']->addRow($button);
                }
            }
        }
    }

    private function normalizeLink(string &$link)
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
        $headers['User-Agent'] .= "; Telegram-AMap-Link-Tracker-Remover/0.1.0";
        return Http::
        withHeaders($headers)
            ->withoutRedirecting()
            ->get($link)
            ->header('Location');
    }
}
