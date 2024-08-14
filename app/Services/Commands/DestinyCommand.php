<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class DestinyCommand extends BaseCommand
{
    public string $name = 'destinylogin';
    public string $description = 'Login your bungie account';
    public string $usage = '/destinylogin';
    public bool $private = true;

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
            'protect_content' => true,
            'reply_markup' => new InlineKeyboard([]),
        ];
        $data['text'] .= 'Please click this button to bind your Bungie Account.';
        $loginButton = new InlineKeyboardButton([
            'text' => 'Bind Your Bungie Account',
            'url' => 'https://tgapi.desmg.org/api/bungie/oauth/login?user_id=' . $chatId,
        ]);
        $data['reply_markup']->addRow($loginButton);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
