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

class VSCode implements SoftwareInterface
{
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
            'text' => "$emoji A new version of VSCode($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => "https://github.com/microsoft/vscode/releases/tag/$version",
        ]);
        $message['reply_markup']->addRow($button1);
        $button2 = new InlineKeyboardButton([
            'text' => 'Windows x86_64 LOCAL_MACHINE',
            'url' => 'https://code.visualstudio.com/sha/download?build=stable&os=win32-x64',
        ]);
        $message['reply_markup']->addRow($button2);
        $button3 = new InlineKeyboardButton([
            'text' => 'Windows x86_64 CURRENT_USER',
            'url' => 'https://code.visualstudio.com/sha/download?build=stable&os=win32-x64-user',
        ]);
        $message['reply_markup']->addRow($button3);
        $button4 = new InlineKeyboardButton([
            'text' => 'macOS Apple Silicon Zip',
            'url' => 'https://code.visualstudio.com/sha/download?build=stable&os=darwin-arm64',
        ]);
        $message['reply_markup']->addRow($button4);
        return $message;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $data = $this->getJson();
        if (!is_array($data)) {
            return Common::getLastVersion(Software::VSCode);
        }
        $version = '0.0.0';
        foreach ($data as $branch) {
            $versionstring = $branch['tag_name'];
            if (version_compare($versionstring, $version, '>')) {
                $version = $versionstring;
            }
        }
        return $version;
    }

    /**
     * @return array|int|false
     */
    private function getJson(): array|int|false
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-VSCode-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->accept('application/vnd.github+json')
            ->withToken(env('GITHUB_TOKEN'))
            ->get('https://api.github.com/repos/microsoft/vscode/releases?per_page=5');
        if ($get->status() == 200) {
            return $get->json();
        }
        return false;
    }
}
