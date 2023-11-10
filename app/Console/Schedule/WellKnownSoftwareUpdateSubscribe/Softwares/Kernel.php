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

class Kernel implements SoftwareInterface
{
    /**
     * @param int    $chat_id
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
            'text' => "$emoji A new version of Kernel($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => 'https://www.kernel.org/',
        ]);
        $message['reply_markup']->addRow($button1);
        return $message;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-Kernel-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->accept('text/html')
            ->get('https://www.kernel.org/feeds/kdist.xml');
        $version = '0.0.0';
        if ($get->status() == 200) {
            $data = $get->body();
            $xml = (array)simplexml_load_string($data);
            $channel = (array)$xml['channel'];
            $items = (array)$channel['item'];
            foreach ($items as $item) {
                $item = (array)$item;
                $title = $item['title'];
                if (str_ends_with($title, 'stable')) {
                    $versionstring = explode(':', $title)[0];
                    if ($version == '0.0.0' || version_compare($versionstring, $version, '>')) {
                        $version = $versionstring;
                    }
                }
            }
        }
        return $version;
    }
}
