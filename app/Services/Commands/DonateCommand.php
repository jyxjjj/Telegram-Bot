<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class DonateCommand extends BaseCommand
{
    public string $name = 'donate';
    public string $description = 'Donate Infomation';
    public string $usage = '/donate';

    /**
     * @param Message  $message
     * @param Telegram $telegram
     * @param int      $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= "\n✥  ✥  ✥  <b>Donate Infomation</b>  ✥  ✥  ✥";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  Thank you for donate this project";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['reply_markup'] = new InlineKeyboard([]);
        $button = new InlineKeyboardButton([
            'text' => 'Donate',
            'url' => 'https://www.desmg.com/donate',
        ]);
        $data['reply_markup']->addRow($button);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
