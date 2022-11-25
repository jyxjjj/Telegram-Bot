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

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $text = $message->getText() ?? $message->getCaption();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $this->handle($text, $data);
        isset($data['reply_markup']) && $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function handle(string $text, array &$data)
    {
        $pattern = '/(http(s)?:\/\/)?(b23\.tv|(www\.|live\.|space\.)?bilibili\.com)\/(video\/)?(read\/)?(mobile\/)?[a-zA-Z\d]+(\?p=(\d){1,3})?/';
        $pattern_av = '/https:\/\/www\.bilibili\.com\/(video\/)?(av|AV)\d+/';
        $pattern_bv = '/https:\/\/www\.bilibili\.com\/(video\/)?(bv|BV)[a-zA-Z\d]+/';
        $pattern_cv = '/https:\/\/www\.bilibili\.com\/(read\/)?(mobile\/)?(cv|CV)?\d+/';
        $pattern_space = '/https:\/\/space\.bilibili\.com\/\d+/';
        $pattern_live = '/https:\/\/live\.bilibili\.com\/[a-zA-Z\d]+/';
        if (preg_match_all($pattern, $text, $matches)) {
            $data['text'] .= "Bilibili Tracker Removed\n";
            $data['text'] .= "<b>Warning</b>: Beta Function, if error occured, contact @jyxjjj .\n";
            if (count($matches[0]) > 3) {
                $count = 3;
            } else {
                $count = count($matches[0]);
            }
            for ($i = 0; $i < $count; $i++) {
                $link = $matches[0][$i];
                $this->normalizeLink($link);
                if (
                    !preg_match($pattern_av, $link) &&
                    !preg_match($pattern_bv, $link) &&
                    !preg_match($pattern_cv, $link) &&
                    !preg_match($pattern_space, $link) &&
                    !preg_match($pattern_live, $link)
                ) {
                    $link = $this->getLocation($link);
                }
                if (
                    preg_match($pattern_av, $link, $matchedLocation) ||
                    preg_match($pattern_bv, $link, $matchedLocation) ||
                    preg_match($pattern_cv, $link, $matchedLocation) ||
                    preg_match($pattern_space, $link, $matchedLocation) ||
                    preg_match($pattern_live, $link, $matchedLocation)
                ) {
                    !isset($data['reply_markup']) && $data['reply_markup'] = new InlineKeyboard([]);
                    $data['text'] .= "<b>Link</b>: <code>$matchedLocation[0]</code>\n";
                    $button = new InlineKeyboardButton([
                        'text' => $matchedLocation[0],
                        'url' => $matchedLocation[0],
                    ]);
                    $data['reply_markup']->addRow($button);
                }
            }
        }
    }

    private function normalizeLink(string &$link)
    {
        if (str_starts_with($link, 'http://')) {
            $link = str_replace('http://', 'https://', $link);
        }
        if (str_starts_with($link, 'b23.tv') || str_starts_with($link, 'bilibili.com') || str_starts_with($link, 'www.bilibili.com') || str_starts_with($link, 'live.bilibili.com')) {
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
