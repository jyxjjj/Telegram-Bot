<?php

namespace App\Services\Keywords;

use App\Jobs\SendMessageJob;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class HelpKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return $message->getChat()->isPrivateChat() && $message->getText() === '帮助与反馈';
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $data = [
            'chat_id' => $chatId,
//            'text' => $this->getHelp($param),
            'text' => '',
        ];
        $data['text'] .= "你的用户ID： $userId";
        $data['reply_markup'] = new InlineKeyboard([]);
        $button1 = new InlineKeyboardButton([
            'text' => 'DMCA Request',
            'url' => 'https://t.me/zaihua_bot',
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => '版权反馈',
            'url' => 'https://t.me/zaihua_bot',
        ]);
        $button3 = new InlineKeyboardButton([
            'text' => '意见建议',
            'url' => 'https://t.me/zaihua_bot',
        ]);
        $button4 = new InlineKeyboardButton([
            'text' => '技术支持',
            'url' => 'https://t.me/jyxjjj',
        ]);
        $data['reply_markup']->addRow($button1, $button2);
        $data['reply_markup']->addRow($button3, $button4);
        $data['text'] && $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
