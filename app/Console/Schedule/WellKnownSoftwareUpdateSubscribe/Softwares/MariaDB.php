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

class MariaDB implements SoftwareInterface
{
    /**
     * @return string
     */
    public function getVersion(): string
    {
        $majors = $this->getMajor();
        $majors = $majors['major_releases'];
        $major = 0;
        $minor = 0;
        foreach ($majors as $release) {
            if ($release['release_status'] == 'Stable') {
                $release_id = $release['release_id'];
                if (preg_match('/^(\d+)\.(\d+)$/i', $release_id, $matches)) {
                    if ($matches[1] > $major) {
                        $major = $matches[1];
                        $minor = $matches[2];
                    } elseif ($matches[1] == $major && $matches[2] > $minor) {
                        $minor = $matches[2];
                    }
                }
            }
        }
        $release_id = "$major.$minor";
        $release = $this->getLatest($release_id);
        $release = $release['releases'];
        $version = '';
        foreach ($release as $key => $value) {
            $version = $key;
            break;
        }
        return $version;
    }

    /**
     * @return array
     */
    private function getMajor(): array
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= "; Telegram-MariaDB-Subscriber-Runner/$ts";
        return Http::
        withHeaders($headers)
            ->get('https://downloads.mariadb.org/rest-api/mariadb/')
            ->json();
    }

    /**
     * @param string $release_id
     * @return array
     */
    private function getLatest(string $release_id): array
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= "; Telegram-MariaDB-Subscriber-Runner/$ts";
        return Http::
        withHeaders($headers)
            ->get("https://downloads.mariadb.org/rest-api/mariadb/$release_id/latest")
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
            'text' => "$emoji A new version of MariaDB($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => "https://mariadb.org/download/?t=mariadb&p=mariadb&r=$version&os=source",
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => 'Download',
            'url' => "https://downloads.mariadb.org/rest-api/mariadb/$version/mariadb-$version.tar.gz",
        ]);
        $message['reply_markup']->addRow($button1, $button2);
        return $message;
    }
}
