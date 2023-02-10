<?php

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares;

use App\Common\Config;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class NodeJS implements SoftwareInterface
{
    public function getVersion(): string
    {
        $version = '0.0.0';
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-NodeJS-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->accept('text/plain')
            ->get('https://nodejs.org/dist/latest/SHASUMS256.txt');
        if ($get->status() == 200) {
            $data = $get->body();
            $data = str_replace('  ', ' ', $data);
            $data = explode("\n", $data);
            foreach ($data as $item) {
                $item = explode(' ', $item);
                if (str_starts_with($item[1], 'node-v') && str_ends_with($item[1], '-linux-x64.tar.gz')) {
                    $version = str_replace(['node-v', '-linux-x64.tar.gz'], '', $item[1]);
                    break;
                }
            }
        }
        return $version;
    }

    /**
     * @param int $chat_id
     * @param string $version
     * @return array
     */
    #[ArrayShape([
        'chat_id' => 'int',
        'text' => 'string',
        'reply_markup' => InlineKeyboard::class,
    ])]
    public function generateMessage(int $chat_id, string $version): array
    {
        $emoji = Common::emoji();
        $message = [
            'chat_id' => $chat_id,
            'text' => "$emoji A new version of NodeJS($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => 'https://nodejs.org/en/download/',
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => 'Download',
            'url' => "https://nodejs.org/dist/latest/node-v$version-linux-x64.tar.gz",
        ]);
        $message['reply_markup']->addRow($button1, $button2);
        return $message;
    }
}
