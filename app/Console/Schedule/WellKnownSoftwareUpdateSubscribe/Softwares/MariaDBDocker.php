<?php

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares;

use App\Common\Config;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class MariaDBDocker implements SoftwareInterface
{
    /**
     * @return string
     */
    public function getVersion(): string
    {
        $latest = $this->getLatest();
        foreach ($latest as $item) {
            if ($item['architecture'] == 'amd64') {
                $layers = $item['layers'];
                foreach ($layers as $layer) {
                    if (preg_match('/^.*MARIADB_VERSION=.*:(\d+\.\d+\.\d+)\+.*$/i', $layer['instruction'], $matches)) {
                        return $matches[1];
                    }
                }
                break;
            }
        }
        return Common::getLastVersion(Software::MariaDBDocker);
    }

    /**
     * @return array
     */
    private function getLatest(): array
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= "; Telegram-MariaDBDocker-Subscriber-Runner/$ts";
        return Http::
        withHeaders($headers)
            ->get('https://registry.hub.docker.com/v2/repositories/library/mariadb/tags/latest/images')
            ->json();
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
            'text' => "$emoji A new version of MariaDB Docker($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => 'https://hub.docker.com/_/mariadb',
        ]);
        $message['reply_markup']->addRow($button1);
        return $message;
    }
}
