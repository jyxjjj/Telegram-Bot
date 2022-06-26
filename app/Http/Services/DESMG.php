<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class DESMG
{
    #[ArrayShape([
        'chat_id' => "int",
        'parse_mode' => "string",
        'disable_web_page_preview' => "bool",
        'allow_sending_without_reply' => "bool",
        'reply_markup' => "\Longman\TelegramBot\Entities\InlineKeyboard",
        'text' => "string"
    ])]
    public static function about(array $data, int $chatId): array
    {
        $commits = Http::accept('application/vnd.github.v3+json')
            ->get('https://api.github.com/repos/jyxjjj/Telegram-Bot/commits?per_page=1')
            ->json();
        $commits = $commits[0];
        $version = substr(strtoupper($commits['sha']), 0, 7);
        $date = date('Y-m-d H:i:s', strtotime($commits['commit']['committer']['date']));
        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ];
        $data['text'] = "龙缘特种工业集团龙缘科技Telegram机器人
龙缘科技 版权所有
Copyright (C) " . date('Y') . "
DESMG All rights reserved.
DESMG Main API(DESMG)
当前版本: $version
版本更新时间: $date
当前时间: " . date('Y-m-d H:i:s') . "
设备名称: " . php_uname('n') . "
系统版本: " . php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname('m') . "
PHP版本: " . PHP_VERSION . " " . PHP_SAPI . " " . PHP_OS . "
";
        $usage = new InlineKeyboardButton([
            'text' => '使用条款',
            'url' => 'https://www.desmg.com/policies#usage',
        ]);
        $privacy = new InlineKeyboardButton([
            'text' => '隐私政策',
            'url' => 'https://www.desmg.com/policies#privacy',
        ]);
        $website = new InlineKeyboardButton([
            'text' => '官方网站',
            'url' => 'https://www.desmg.com',
        ]);
        $contact = new InlineKeyboardButton([
            'text' => '联系我们',
            'url' => 'https://t.me/jyxjjj',
        ]);
        $channel = new InlineKeyboardButton([
            'text' => 'Telegram频道',
            'url' => 'https://t.me/desmg',
        ]);
        $group = new InlineKeyboardButton([
            'text' => 'Telegram群',
            'url' => 'https://t.me/desmg_official',
        ]);
        $data['reply_markup'] = new InlineKeyboard([]);
        $data['reply_markup']->addRow($usage, $privacy);
        $data['reply_markup']->addRow($website, $contact);
        $data['reply_markup']->addRow($channel, $group);
        return $data;
    }
}
