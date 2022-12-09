<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Models\TStarted;
use App\Services\Base\BaseCommand;
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
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $payload = $message->getText(true);
        /** @var TStarted $startedUser */
        $startedUser = TStarted::addUser($userId);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= "Hello, I am here alive.\n";
        $data['text'] .= "Type /help to get the help.\n";
        $data['text'] .= "Type /about to get the open source information, Privacy Policies, Usage Agreements.\n";
        $data['text'] .= "This bot is none of any of your groups' business, it is free for all and can be set by third parties to do anything it can do.\n";
        $data['text'] .= "We do not provide any security promises and data keeps.\n";
        $data['text'] .= "Any questions, please contact @jyxjjj .\n";
        $data['text'] .= "<b>Your user_id</b>: <a href='tg://user?id=$startedUser->user_id'>$startedUser->user_id</a>\n";
        $payload && $data['text'] .= "<b>Your payload</b>: <code>$payload</code>\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
