<?php

namespace App\Services\Commands;

use App\Common\RequestService;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AboutCommand extends BaseCommand
{
    public string $name = 'about';
    public string $description = 'About';
    public string $usage = '/about';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $delete = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];
        RequestService::getInstance()->deleteMessage($delete);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= "云盘盘投稿机器人3.0\n";
        $data['text'] .= sprintf("Copyright (C) %s\n", date('Y'));
        $data['text'] .= "DESMG All rights reserved.\n";
        $data['text'] .= "<b>Version</b>: 3.0\n";
        $data['text'] .= sprintf("<b>System Time</b>: <code>%s</code>\n", date('Y-m-d H:i:s'));
        $data['text'] .= sprintf("<b>Device Name</b>: <code>%s</code>\n", php_uname('n'));
        $data['text'] .= sprintf("<b>System Version</b>: <code>%s %s %s</code>\n", php_uname('s'), php_uname('r'), php_uname('m'));
        $data['text'] .= sprintf("<b>PHP Version</b>: <code>%s %s %s</code>\n", PHP_VERSION, PHP_SAPI, PHP_OS);
        $data['text'] .= "\n";
        $data['text'] .= "This bot is <i>opensource</i> and can be set by <b>third parties</b> to do <i>anything it can do</i>.\n";
        $data['text'] .= "We <b>do not</b> provide any <i>security promises and data keeps</i>.\n";
        $data['text'] .= "All source codes can be edit or delete by <b>third parties</b>.\n";
        $data['text'] .= "Our licence don't allow <b>third parties</b> to edit <i>copyright and donate infomation</i>.\n";
        $data['text'] .= "If <b>third parties</b> use it to perform illegal actions, <a href='https://t.me/jyxjjj'>click here</a> to report.\n";
        $data['text'] .= "\n";
        $data['text'] .= "<b>DMCA</b>";
        $data['text'] .= "\n";
        $data['text'] .= "If you want to Initiate a DMCA request, please send \"<code>DMCA Requst</code>\" to this bot,\n";
        $data['text'] .= "Or you can click the button which titled as \"<code>DMCA Requst</code>\".\n";
        $data['reply_markup'] = new InlineKeyboard([]);
        $github = new InlineKeyboardButton([
            'text' => 'GitHub',
            'url' => 'https://github.com/jyxjjj/Telegram-Bot/tree/YPBot',
        ]);
        $data['reply_markup']->addRow($github);
        $channel = new InlineKeyboardButton([
            'text' => '阿里云盘盘',
            'url' => 'https://t.me/zaihuapan',
        ]);
        $group = new InlineKeyboardButton([
            'text' => '官方群',
            'url' => 'https://t.me/yppshare',
        ]);
        $data['reply_markup']->addRow($channel, $group);
        RequestService::getInstance()->sendMessage($data);
    }
}
