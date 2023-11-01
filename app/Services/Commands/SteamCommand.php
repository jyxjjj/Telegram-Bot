<?php

namespace App\Services\Commands;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class SteamCommand extends BaseCommand
{
    public string $name = 'steam';
    public string $description = 'Show the price of steam game id';
    public string $usage = '/steam {AppID} [CountryCode=CN]';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $param = $message->getText(true);
        if ($param == '') {
            $data['text'] .= "<b>Error</b>: You should provide an AppID for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        $params = explode(' ', $param);
        $appId = $params[0];
        $countryCode = $params[1] ?? 'CN';
        $data['text'] .= "<b>AppID</b>: $appId\n";
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-Steam-Price-Checker/$ts";
        $http = Http::
        baseUrl("https://store.steampowered.com")
            ->withQueryParameters([
                'appids' => $appId,
                'filters' => 'price_overview',
                'cc' => $countryCode,
            ])
            ->withHeaders($headers)
            ->get("/api/appdetails")
            ->json();
        $currency = $http[$appId]['data']['price_overview']['currency'];
        $originalPrice = bcdiv($http[$appId]['data']['price_overview']['initial'], 100, 2);
        $finalPrice = bcdiv($http[$appId]['data']['price_overview']['final'], 100, 2);
        $discountPercent = bcmul($http[$appId]['data']['price_overview']['discount_percent'], 100, 0);
        $formattedOriginalPrice = $http[$appId]['data']['price_overview']['initial_formatted'];
        $formattedFinalPrice = $http[$appId]['data']['price_overview']['final_formatted'];
        $data['text'] .= "<b>Currency</b>: $currency\n";
        if ($discountPercent == 0) {
            $data['text'] .= "<b>Price</b>: $finalPrice\n";
            $data['text'] .= "<b>Formatted Price</b>: $formattedFinalPrice\n";
        } else {
            $data['text'] .= "<b>Original Price</b>: $originalPrice\n";
            $data['text'] .= "<b>Final Price</b>: $finalPrice\n";
            $data['text'] .= "<b>Discount Percent</b>: $discountPercent%\n";
            $data['text'] .= "<b>Formatted Original Price</b>: $formattedOriginalPrice\n";
            $data['text'] .= "<b>Formatted Final Price</b>: $formattedFinalPrice\n";
        }
        $data['reply_markup'] = new InlineKeyboard([]);
        $link = new InlineKeyboardButton([
            'text' => 'View On Steam',
            'url' => "https://store.steampowered.com/app/$appId",
        ]);
        $data['reply_markup']->addRow($link);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
