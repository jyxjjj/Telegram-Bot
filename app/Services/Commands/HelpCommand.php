<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class HelpCommand extends BaseCommand
{
    public string $name = 'help';
    public string $description = 'Show commands list';
    public string $usage = '/help';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $data = [
            'chat_id' => $chatId,
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
