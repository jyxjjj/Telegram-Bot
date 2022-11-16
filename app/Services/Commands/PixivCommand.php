<?php

namespace App\Services\Commands;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Jobs\SendPhotoJob;
use App\Services\Base\BaseCommand;
use DESMG\RFC6986\Hash;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Throwable;

class PixivCommand extends BaseCommand
{
    public string $name = 'pixiv';
    public string $description = 'Get a pic from pixiv';
    public string $usage = '/pixiv';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => "*Error:* This command is available only for groups.\n",
            ];
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        [$photo, $caption] = $this->getPhotoAndCaption();
        if ($photo && $caption) {
            try {
                Log::debug($photo);
                $photo = Request::encodeFile($photo);
                if (!$photo) {
                    throw new Exception('Photo encode failed.');
                }
            } catch (Throwable) {
                $data = [
                    'chat_id' => $chatId,
                    'reply_to_message_id' => $messageId,
                    'text' => "*Error:* Get a random picture failed when encoding file.\n",
                ];
                $this->dispatch(new SendMessageJob($data));
                return;
            }
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'photo' => $photo,
                'caption' => $caption,
                'protect_content' => true,
            ];
            $this->dispatch(new SendPhotoJob($data, 0));
        } else {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => "*Error:* Get a random picture failed.\n",
            ];
            $this->dispatch(new SendMessageJob($data));
        }
    }

    private function getPhotoAndCaption(): array
    {
        $storage = Storage::disk('public');
        $now = Carbon::now();
        $date = $now->clone()->format('Y-m-d');
        $path = "pixiv/{$date}.json";
        if (!$storage->exists($path)) {
            $date = $now->clone()->subDay()->format('Y-m-d');
            $path = "pixiv/{$date}.json";
            if (!$storage->exists($path)) {
                $date = $now->clone()->subDays(2)->format('Y-m-d');
                $path = "pixiv/{$date}.json";
                if (!$storage->exists($path)) {
                    return [null, null];
                }
            }
        }
        $json = $storage->get($path);
        $data = json_decode($json, true);
        $date = $data['date'];
        $index = array_rand($data['data']);
        $item = $data['data'][$index];
        $title = $item['title'];
        $artwork_url = $item['artwork_url'];
        $author = $item['author'];
        $author_url = $item['author_url'];
        $url = $item['url'];
        $caption = "Artwork: [{$title}]({$artwork_url})\n";
        $caption .= "Author: [{$author}]({$author_url})\n";
        $caption .= "Source: {$url}\n";
        $caption .= "Date: {$date}\n";
        try {
            $path = $this->download($url);
            if ($path) {
                return [$path, $caption];
            }
            return [null, null];
        } catch (Throwable) {
            return [null, null];
        }
    }

    private function download($url): ?string
    {
        $headers = Config::CURL_HEADERS;
        $headers['Referer'] = 'https://www.pixiv.net/ranking.php?mode=daily';
        $response = Http::withHeaders($headers)
            ->connectTimeout(10)
            ->timeout(10)
            ->retry(3, 1000)
            ->get($url);
        if ($response->successful()) {
            $body = $response->body();
            $name = Hash::sha256($body);
            $path = "pixiv/{$name}.jpg";
            $s = Storage::disk('public')->put($path, $body);
            Log::debug($s);
            return Storage::disk('public')->path($path);
        }
        return null;
    }
}
