<?php

namespace App\Http\Services\Bots\Plugins;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class ZaiHuaBot
{
    public static function newMemberMessage(array &$data, int $chatId, string $newChatMemberName, int $newChatMemberId)
    {
        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ];
        $text = "🎉 欢迎 [$newChatMemberName](tg://user?id=$newChatMemberId) 加入 科技花🌸 群组
🍜 饿了么、美团外卖每日红包 [地址](https://mai.zaihua.me)
💊 如若被机器人误杀，请 [联系](https://t.me/zaihua) 解封";
        $data['text'] = $text;
        $zaihua_contribute = new InlineKeyboardButton([
            'text' => '📮科技花投稿',
            'url' => 'https://t.me/zaihuabot',
        ]);
        $zaihua_channel = new InlineKeyboardButton([
            'text' => '📣科技花频道',
            'url' => 'https://t.me/TestFlightCN',
        ]);
        $zaihua_jd = new InlineKeyboardButton([
            'text' => '🧲京东优选',
            'url' => 'https://t.me/zaihuajd',
        ]);
        $zaihua_tb = new InlineKeyboardButton([
            'text' => '🚧淘宝优选',
            'url' => 'https://t.me/zaihuatb',
        ]);
        $zaihua_wool = new InlineKeyboardButton([
            'text' => '🎊京豆',
            'url' => 'https://t.me/zhwool',
        ]);
        $data['reply_markup'] = new InlineKeyboard([]);
        $data['reply_markup']->addRow($zaihua_contribute, $zaihua_channel);
        $data['reply_markup']->addRow($zaihua_jd, $zaihua_tb);
        $data['reply_markup']->addRow($zaihua_wool);
    }
}
