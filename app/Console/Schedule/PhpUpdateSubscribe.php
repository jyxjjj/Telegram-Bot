<?php

namespace App\Console\Schedule;

use App\Common\Config;
use App\Exceptions\Handler;
use App\Jobs\SendMessageJob;
use App\Models\TUpdateSubscribes;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Throwable;

class PhpUpdateSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:php';
    protected $description = 'Get PHP Newest Version then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $version = $this->getVersion();
            if (strlen($version) < 5) {
                return self::SUCCESS;
            }
            /** @var TUpdateSubscribes[] $datas */
            $datas = TUpdateSubscribes::getAllSubscribeBySoftware('PHP');
            foreach ($datas as $data) {
                $chat_id = $data->chat_id;
                $message = [
                    'chat_id' => $chat_id,
                    'text' => '',
                ];
                $emoji = '["\ud83c\udf89"]';
                $emoji = json_decode($emoji, true);
                $emoji = $emoji[0];
                $message['text'] .= "{$emoji}{$emoji}{$emoji}New PHP Release{$emoji}{$emoji}{$emoji}\n";
                $message['text'] .= "New version: `{$version}`\n";
                $message['reply_markup'] = new InlineKeyboard([]);
                $button1 = new InlineKeyboardButton([
                    'text' => 'View',
                    'url' => 'https://www.php.net/downloads.php',
                ]);
                $button2 = new InlineKeyboardButton([
                    'text' => 'Download',
                    'url' => "https://www.php.net/distributions/php-{$version}.tar.gz",
                ]);
                $message['reply_markup']->addRow($button1, $button2);
                $lastSend = $this->getLastSend($chat_id);
                if (!$lastSend) {
                    $this->dispatch(new SendMessageJob($message, null, 0));
                    $this->setLastSend($chat_id, $version);
                } else {
                    if ($lastSend != $version) {
                        $this->dispatch(new SendMessageJob($message, null, 0));
                        $this->setLastSend($chat_id, $version);
                    }
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            Handler::logError($e);
            return self::FAILURE;
        }
    }

    /**
     * @return string
     */
    private function getVersion(): string
    {
        $data = $this->getJson();
        if (!is_array($data)) {
            return $this->getLastVersion();
        }
        $major = 0;
        $minor = 0;
        $patch = 0;
        foreach ($data as $branch) {
            if (preg_match('/^PHP-(\d+)\.(\d+)\.(\d+)$/i', $branch['name'], $matches)) {
                if ($matches[1] > $major) {
                    $major = $matches[1];
                    $minor = $matches[2];
                    $patch = $matches[3];
                } elseif ($matches[1] == $major && $matches[2] > $minor) {
                    $minor = $matches[2];
                    $patch = $matches[3];
                } elseif ($matches[1] == $major && $matches[2] == $minor && $matches[3] > $patch) {
                    $patch = $matches[3];
                }
            }
        }
        $version = "{$major}.{$minor}.{$patch}";
        $this->setLastVersion($version);
        return $version;
    }

    /**
     * @return array|int|false
     */
    private function getJson(): array|int|false
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= "; Telegram-PHP-Subscriber-Runner/$ts";
        $last_modified = $this->getLastModified();
        if ($last_modified) {
            $headers['If-Modified-Since'] = $last_modified;
        }
        $get = Http::
        withHeaders($headers)
            ->accept('application/vnd.github+json')
            ->withToken(env('GITHUB_TOKEN'))
            ->get('https://api.github.com/repos/php/php-src/tags?per_page=100');
        $last_modified = $get->header('last-modified');
        $this->cacheLastModified($last_modified);
        if ($get->status() == 200) {
            return $get->json();
        }
        if ($get->status() == 304) {
            return 304;
        }
        return false;
    }

    /**
     * @return string|false
     */
    private function getLastModified(): string|false
    {
        return Cache::get('Schedule::UpdateSubscribe::last_modified::PHP', false);
    }

    /**
     * @param string $lastModified
     * @return bool
     */
    private function cacheLastModified(string $lastModified): bool
    {
        return Cache::put('Schedule::UpdateSubscribe::last_modified::PHP', $lastModified, Carbon::now()->addMonths(3));
    }

    private function getLastVersion(): string
    {
        return Cache::get('Schedule::UpdateSubscribe::last_version::PHP', '');
    }

    private function setLastVersion(string $version): bool
    {
        return Cache::put('Schedule::UpdateSubscribe::last_version::PHP', $version, Carbon::now()->addMonths(3));
    }

    /**
     * @param int $chat_id
     * @return string|false
     */
    private function getLastSend(int $chat_id): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_send::{$chat_id}::PHP", false);
    }

    /**
     * @param int $chat_id
     * @param string $version
     * @return bool
     */
    private function setLastSend(int $chat_id, string $version): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_send::{$chat_id}::PHP", $version, Carbon::now()->addMonths(3));
    }
}
