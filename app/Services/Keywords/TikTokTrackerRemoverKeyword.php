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

class TikTokTrackerRemoverKeyword extends BaseKeyword
{
    public string $name = 'TikTok tracker remover';
    public string $description = 'Remove TikTok tracker from douyin link';
    public string $version = '1.0.0';
    protected string $pattern = '/(v\.douyin\.com)/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $data['text'] .= 'TikTok Tracker Remover v1.0.0 [Alpha]' . PHP_EOL;
        $entities = $this->getUrls($message);
        $this->handle($entities, $data);
        isset($data['reply_markup']) && $this->dispatch(new SendMessageJob($data, null, 0));
    }

    /**
     * @param Message $message
     * @return array
     */
    protected function getUrls(Message $message): array
    {
        $text = $message->getText() ?? $message->getCaption();
        $entities = $message->getEntities() ?? $message->getCaptionEntities() ?? [];
        $urls = [];
        foreach ($entities as $entity) {
            if ($entity->getType() === 'url') {
                $offset = $entity->getOffset();
                $length = $entity->getLength();
                $url = mb_substr($text, $offset, $length);
                if (!str_starts_with($url, 'http')) {
                    $url = 'https://' . $url;
                }
                $urls[] = $url;
            }
            if ($entity->getType() === 'text_link') {
                $url = $entity->getUrl();
                if (!str_starts_with($url, 'http')) {
                    $url = 'https://' . $url;
                }
                $urls[] = 'https://' . $url;
            }
        }
        return array_filter($urls);
    }

    /**
     * @param array $entities
     * @param array $data
     * @return void
     */
    private function handle(array $entities, array &$data): void
    {
        if (count($entities) > 3) {
            $entities = array_slice($entities, 0, 3);
        }
        foreach ($entities as $entity) {
            $url = parse_url($entity);
            if (!$url) {
                return;
            }
            if ($url['scheme'] == 'http') {
                $entity = str_replace('http://', 'https://', $entity);
            }
            if ($url['host'] != 'v.douyin.com') {
                continue;
            }
            $entity = $this->getLocation($entity);
            $link = $this->removeAllParams($entity);
            $data['text'] .= $link . PHP_EOL;
            $button = new InlineKeyboardButton([
                'text' => $id ?? $link,
                'url' => $link,
            ]);
            !isset($data['reply_markup']) && $data['reply_markup'] = new InlineKeyboard([]);
            $data['reply_markup']->addRow($button);
        }
    }

    /**
     * @param string $link
     * @return string header Location
     */
    private function getLocation(string $link): string
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-TikTok-Link-Tracker-Remover/$this->version";
        $location = Http::
        withHeaders($headers)
            ->withoutRedirecting()
            ->get($link)
            ->header('Location');
        return $location ?: $link;
    }

    /**
     * @param string $link
     * @return string
     */
    private function removeAllParams(string $link): string
    {
        $url = parse_url($link);
        return str_replace('https://www.iesdouyin.com/share/video/', 'https://www.douyin.com/video/', "https://{$url['host']}{$url['path']}");
    }

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }
}
