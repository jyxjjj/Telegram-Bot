<?php

namespace App\Services\Keywords;

use App\Common\BotCommon;
use App\Jobs\SendMessageJob;
use App\Services\BaseKeyword;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use function App\Http\Services\Keywords\str_starts_with;

class B23TrackerRemoverKeyword extends BaseKeyword
{
    public string $name = 'b23_tracker_remover';
    public string $description = 'Remove b23 tracker from b23 link';
    public string $pattern = '/((http(s?):\/\/)?)b23.tv\/([a-zA-Z\d]+)/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $messageId = BotCommon::getMessageId($message);
        $text = $message->getText();
        if (preg_match($this->pattern, $text, $matches)) {
            Log::debug('B23TrackerRemoverKeyword: matches', $matches);
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
                $data['text'] .= "*B23 Tracker Removed!*\n";
                $data['text'] .= "*Link:* `$matches[0]`\n";
                $data['text'] .= "[Click here to open]($matches[0])\n";
                $this->dispatch(new SendMessageJob($data, null, 0));
            }
        }
    }
}
