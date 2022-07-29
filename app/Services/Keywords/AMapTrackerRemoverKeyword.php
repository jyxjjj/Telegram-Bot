<?php

namespace App\Services\Keywords;

use App\Common\BotCommon;
use App\Jobs\SendMessageJob;
use App\Services\BaseKeyword;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AMapTrackerRemoverKeyword extends BaseKeyword
{
    public string $name = 'AMap tracker remover';
    public string $description = 'Remove AMap tracker from surl link';
    protected string $pattern = '/(surl\.amap\.com)/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $messageId = BotCommon::getMessageId($message);
        $text = $message->getText();
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
            $location = Http::
            withUserAgent('Telegram-B23-Link-Tracker-Remover' . $this->version)
                ->withoutRedirecting()
                ->get($matches[0])
                ->header('Location');
            if ($location != '' && preg_match('/https:\/\/www.bilibili.com\/video\/[a-zA-Z\d]+/', $location, $matches)) {
                $data['text'] .= "AMap Tracker Removed\n\n";
                $data['text'] .= "*Link:* `$matches[0]`\n\n";
                $data['text'] .= "[Click here to open]($matches[0])";
                $this->dispatch(new SendMessageJob($data, null, 0));
            }
        }
    }

    public function preExecute(string $text): bool
    {
        // TODO: Implement preExecute() method.
        return false;
    }
}
