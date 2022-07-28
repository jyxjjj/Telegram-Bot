<?php

namespace App\Console\Commands;

use App\Common\BotCommon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class TestSendMessage extends Command
{
    protected $signature = 'command:testSendMessage';
    protected $description = 'Test Send Message';

    /**
     * @return int
     * @throws TelegramException
     */
    public function handle(): int
    {
        BotCommon::getTelegram();
        Request::setClient(
            new Client(
                [
                    'base_uri' => env('TELEGRAM_API_BASE_URI'),
                    'timeout' => 10,
                    'proxy' => 'socks5h://127.0.0.1:7890',
                ]
            )
        );
        $data = [
            'chat_id' => '886776929',
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
            'text' => '',
        ];
        $data['text'] .= "TEST\n";
        $data['reply_markup'] = new InlineKeyboard([]);
        $button1 = new InlineKeyboardButton([
            'text' => 'GitHub',
            'url' => 'https://github.com',
        ]);
        $data['reply_markup']->addRow($button1);
        dd(Request::sendMessage($data));
        return self::SUCCESS;
    }
}
