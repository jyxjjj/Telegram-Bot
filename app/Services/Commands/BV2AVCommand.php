<?php

namespace App\Services\Commands;

use App\Common\B23;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BV2AVCommand extends BaseCommand
{
    public string $name = 'bv2av';
    public string $description = 'BV to AV';
    public string $usage = '/bv2av {BVID}';

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
        $param = $message->getText(true);
        $avid = B23::BV2AV($param);
        $link = "https://b23.tv/$avid";
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
            'reply_markup' => new InlineKeyboard([]),
        ];
        $data['text'] .= "AVID: <code>$avid</code>\n";
        $data['text'] .= "Link: <code>$link</code>\n";
        $button = new InlineKeyboardButton(['text' => $avid, 'url' => $link]);
        $data['reply_markup']->addRow($button);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
