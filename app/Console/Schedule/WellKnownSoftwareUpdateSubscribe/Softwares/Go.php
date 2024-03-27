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

class Go implements SoftwareInterface
{
    const string GQL = <<<EOF
{
  repository(owner: "golang", name: "go") {
    refs(
      refPrefix: "refs/tags/"
      last: 5
      orderBy: {field: TAG_COMMIT_DATE, direction: ASC}
    ) {
      nodes {
        name
      }
    }
  }
}
EOF;

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
            'text' => "$emoji A new version of Go($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button = new InlineKeyboardButton([
            'text' => 'View',
            'url' => 'https://github.com/golang/go',
        ]);
        $message['reply_markup']->addRow($button);
        return $message;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $data = $this->getJson();
        if (!is_array($data)) {
            dump($data);
            return Common::getLastVersion(Software::Go);
        }
        $major = 0;
        $minor = 0;
        $patch = 0;
        $data = $data['data']['repository']['refs']['nodes'];
        foreach ($data as $branch) {
            if (preg_match('/^go(\d+)\.(\d+)\.(\d+)$/i', $branch['name'], $matches)) {
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
        return "$major.$minor.$patch";
    }

    /**
     * @return array|false
     */
    private function getJson(): array|false
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-Go-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->withToken(env('GITHUB_TOKEN'))
            ->post('https://api.github.com/graphql', [
                'query' => self::GQL,
            ]);
        if ($get->status() == 200) {
            return $get->json();
        }
        return false;
    }
}
