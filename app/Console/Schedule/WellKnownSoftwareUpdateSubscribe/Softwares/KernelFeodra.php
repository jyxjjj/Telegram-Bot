<?php

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares;

use App\Common\Config;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use App\Exceptions\Handler;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Throwable;

class KernelFeodra implements SoftwareInterface
{
    /**
     * @return string
     */
    public function getVersion(): string
    {
        $baseurl = 'https://eu.edge.kernel.org/fedora/updates/37/Everything/x86_64/Packages/k/';
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-Kernel-Subscriber-Runner/$ts";
        $get = Http::
        withHeaders($headers)
            ->accept('text/html')
            ->get($baseurl);
        $html = $get->body();
        $version = '0.0.0';
        try {
            $dom = new DOMDocument;
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            /** @var DOMNodeList $hrefs */
            $hrefs = $xpath->evaluate("/html/body//a");
            for ($i = 0; $i < $hrefs->length; $i++) {
                /** @var DOMElement $href */
                $href = $hrefs->item($i);
                $url = $href->getAttribute('href');
                if (str_starts_with($url, 'kernel-') && str_contains($url, 'core') && str_ends_with($url, '.rpm')) {
                    $versionstring = explode('-', $url)[2];
                    if ($version == '0.0.0' || version_compare($versionstring, $version, '>')) {
                        $version = $versionstring;
                    }
                }
            }
        } catch (Throwable $e) {
            Handler::logError($e);
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
            'text' => "$emoji A new version of FedoraKernel($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'China USTC Mirror',
            'url' => 'https://mirrors.ustc.edu.cn/fedora/updates/37/Everything/x86_64/Packages/k/',
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => 'Europe Edge Mirror',
            'url' => 'https://eu.edge.kernel.org/fedora/updates/37/Everything/x86_64/Packages/k/',
        ]);
        $message['reply_markup']->addRow($button1, $button2);
        return $message;
    }
}
