<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
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
        if (!$message->getChat()->isPrivateChat()) {
            return;
        }
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= env('LONG_START_AD');
        $data['text'] .= "\n意见反馈、技术支持\n";
        $data['text'] .= "请向本机器人发送 /help 命令\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
