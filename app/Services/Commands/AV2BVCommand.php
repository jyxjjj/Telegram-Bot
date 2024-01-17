<?php

namespace App\Services\Commands;

use App\Common\B23;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AV2BVCommand extends BaseCommand
{
    public string $name = 'av2bv';
    public string $description = 'AV to BV';
    public string $usage = '/av2bv {AVID}';

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
        $bvid = B23::AV2BV($param);
        $link = "https://b23.tv/$bvid";
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
            'reply_markup' => new InlineKeyboard([]),
        ];
        $data['text'] .= "BVID: <code>$bvid</code>" . PHP_EOL;
        $data['text'] .= "Link: <code>$link</code>" . PHP_EOL;
        $button = new InlineKeyboardButton(['text' => $bvid, 'url' => $link]);
        $data['reply_markup']->addRow($button);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }


}
