<?php

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares;

use App\Common\Config;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use Illuminate\Support\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class Kernel implements SoftwareInterface
{
    /**
     * @return string
     */
    public function getVersion(): string
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= "; Telegram-Kernel-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->accept('text/html')
            ->get('https://www.kernel.org/');
        $version = '0.0.0';
        if ($get->status() == 200) {
            $data = $get->body();
            //#latest_link > a
            $html = new DOMDocument();
            @$html->loadHTML($data);
            $xpath = new DOMXPath($html);
            $nodes = $xpath->query('//*[@id="latest_link"]/a');
            if ($nodes->length > 0) {
                $version = $nodes->item(0)->nodeValue;
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
}
