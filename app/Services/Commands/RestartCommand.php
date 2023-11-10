<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Artisan;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class RestartCommand extends BaseCommand
{
    public string $name = 'restart';
    public string $description = 'Restart queue worker';
    public string $usage = '/restart';
    public bool $admin = true;

    /**
     * @param Message  $message
     * @param Telegram $telegram
     * @param int      $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        try {
            $code = Artisan::call('queue:restart');
            $msg = 'Queue worker restarted';
        } catch (Throwable $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $chatId = $message->getChat()->getId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $data['text'] .= "Sent restart signal.\n";
        $data['text'] .= "<b>Returned Code</b>: <code>$code</code>\n";
        $data['text'] .= "<b>Returned Msg</b>: <code>$msg</code>\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
