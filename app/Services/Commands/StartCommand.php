<?php

namespace App\Services\Commands;

use App\Common\BotCommon;
use App\Jobs\SendMessageJob;
use App\Models\TStarted;
use App\Services\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class StartCommand extends BaseCommand
{
    public string $name = 'start';
    public string $description = 'Start command';
    public string $usage = '/start';
    public bool $private = true;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = BotCommon::getChatId($message);
        $userId = BotCommon::getSender($message);
        /* @var \App\Models\TStarted $startedUser */
        $startedUser = TStarted::addUser($userId);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= "Hello, I am here alive.\n";
        $data['text'] .= "Type /help to get the help.\n";
        $data['text'] .= "*Your user_id:* [$startedUser->user_id](tg://user?id=$startedUser->user_id)\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
