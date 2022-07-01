<?php

namespace App\Http\Services\Commands;

use App\Http\Services\BaseCommand;
use App\Jobs\SendMessageJob;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class RestartCommand extends BaseCommand
{
    public string $name = 'restart';
    public string $description = 'Restart queue worker';
    public string $usage = '/restart';
    public bool $private = true;
    public bool $admin = true;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        try {
            $code = Artisan::call('queue:restart');
            $msg = 'Queue worker restarted';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $data['text'] .= "Sent restart signal.\n";
        $data['text'] .= "*Returned Code:* `$code`\n";
        $data['text'] .= "*Returned Msg:* `$msg`\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
