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
        $data['text'] .= "Type /help to get the command list.\n";
        $data['text'] .= "Type /about to get the <b>open source information</b>, <b>privacy policies</b>, <b>usage agreements</b>.\n";
        $data['text'] .= "This bot is <b>none of any of your groups' business</b>, it is <i>free for all</i> and can be set by <b>third parties</b> to do <i>anything it can do</i>.\n";
        $data['text'] .= "We <b>do not</b> provide any <i>security promises and data keeps</i>.\n";
        $data['text'] .= "Any questions, please contact @jyxjjj .\n";
        $data['text'] .= "<b>Your user_id</b>: <a href='tg://user?id=$startedUser->user_id'>$startedUser->user_id</a>\n";
        $payload && $data['text'] .= "<b>Your payload</b>: <code>$payload</code>\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
