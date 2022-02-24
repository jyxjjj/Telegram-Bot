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
        $zaihuaContribute = new InlineKeyboardButton([
            'text' => '📮科技花投稿',
            'url' => 'https://t.me/zaihuabot',
        ]);
        $zaihuaChannel = new InlineKeyboardButton([
            'text' => '📣科技花频道',
            'url' => 'https://t.me/TestFlightCN',
        ]);
        $zaihuaJd = new InlineKeyboardButton([
            'text' => '🧲京东优选',
            'url' => 'https://t.me/zaihuajd',
        ]);
        $zaihuaTb = new InlineKeyboardButton([
            'text' => '🚧淘宝优选',
            'url' => 'https://t.me/zaihuatb',
        ]);
        $zaihuaWool = new InlineKeyboardButton([
            'text' => '🎊京豆',
            'url' => 'https://t.me/zhwool',
        ]);
        $data['reply_markup'] = new InlineKeyboard([]);
        $data['reply_markup']->addRow($zaihuaContribute, $zaihuaChannel);
        $data['reply_markup']->addRow($zaihuaJd, $zaihuaTb);
        $data['reply_markup']->addRow($zaihuaWool);
    }
}
