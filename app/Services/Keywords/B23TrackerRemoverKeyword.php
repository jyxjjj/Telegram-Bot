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

class B23TrackerRemoverKeyword extends BaseKeyword
{
    public string $name = 'b23 tracker remover';
    public string $description = 'Remove b23 tracker from b23 link';
    protected string $pattern = '/(b23\.tv|bilibili\.com)/';

    public function preExecute(string $text): bool
    {
        if (preg_match($this->pattern, $text)) {
            return true;
        }
        return false;
    }

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

    private function handle(string $text, array &$data)
    {
        $pattern = '/(http(s)?:\/\/)?(b23\.tv|(www\.)?bilibili\.com)\/(video\/)?[a-zA-Z\d]+(\?p=(\d){1,3})?/';
        if (preg_match_all($pattern, $text, $matches)) {
            $data['text'] .= "Bilibili Tracker Removed\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (count($matches[0]) > 3) {
                $count = 3;
            } else {
                $count = count($matches[0]);
            }
            for ($i = 0; $i < $count; $i++) {
                $link = $matches[0][$i];
                $this->normalizeLink($link);
                $pattern = '/https:\/\/(www|live).bilibili.com\/(video\/)?[a-zA-Z\d]+(\?p=(\d){1,3})?/';
                if (preg_match($pattern, $link)) {
                    $data['text'] .= "*Link:* `$link`\n";
                    $button = new InlineKeyboardButton([
                        'text' => $link,
                        'url' => $link,
                    ]);
                    $data['reply_markup']->addRow($button);
                } else {
                    $location = $this->getLocation($link);
                    if (preg_match($pattern, $location, $matchedLocation)) {
                        $data['text'] .= "*Link:* `$matchedLocation[0]`\n";
                        $button = new InlineKeyboardButton([
                            'text' => $matchedLocation[0],
                            'url' => $matchedLocation[0],
                        ]);
                        $data['reply_markup']->addRow($button);
                    }
                }
            }
        }
    }

    private function normalizeLink(string &$link)
    {
        if (str_starts_with($link, 'http://')) {
            $link = str_replace('http://', 'https://', $link);
        }
        if (str_starts_with($link, 'b23.tv') || str_starts_with($link, 'bilibili.com') || str_starts_with($link, 'www.bilibili.com')) {
            $link = "https://$link";
        }
    }

    private function getLocation(string $link): string
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= "; Telegram-B23-Link-Tracker-Remover/$this->version";
        return Http::
        withHeaders($headers)
            ->withoutRedirecting()
            ->get($link)
            ->header('Location');
    }
}
