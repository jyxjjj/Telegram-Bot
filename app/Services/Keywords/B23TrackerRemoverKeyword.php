<?php

namespace App\Services\Keywords;

use App\Common\BotCommon;
use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Services\BaseKeyword;
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
    private array $matches;

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $messageId = BotCommon::getMessageId($message);
        $text = BotCommon::getText($message);
        if (preg_match($this->pattern, $text, $matches)) {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => '',
            ];
            if (str_starts_with($matches[0], 'http://')) {
                str_replace('http://', 'https://', $matches[0]);
            }
            if (str_starts_with($matches[0], 'b23.tv')) {
                $matches[0] = 'https://' . $matches[0];
            }
            $headers = Config::CURL_HEADERS;
            $headers['User-Agent'] .= "; Telegram-B23-Link-Tracker-Remover/$this->version";
            $location = Http::
            withHeaders($headers)
                ->withoutRedirecting()
                ->get($matches[0])
                ->header('Location');
            if ($location != '' && preg_match('/https:\/\/www.bilibili.com\/video\/[a-zA-Z\d]+/', $location, $matches)) {
                $data['text'] .= "Bilibili Tracker Removed\n";
                $data['text'] .= "*Link:* `$matches[0]`\n";
                $data['reply_markup'] = new InlineKeyboard([]);
                $button1 = new InlineKeyboardButton([
                    'text' => 'Click here to open',
                    'url' => $matches[0],
                ]);
                $data['reply_markup']->addRow($button1);
                $this->dispatch(new SendMessageJob($data, null, 0));
            }
        }
    }

    public function preExecute(string $text): bool
    {
        if (preg_match($this->pattern, $text, $this->matches)) {
            return true;
        }
        return false;
    }
}
