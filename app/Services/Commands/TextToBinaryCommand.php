<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class TextToBinaryCommand extends BaseCommand
{
    public string $name = 'texttobinary';
    public string $description = 'Show message text in binary';
    public string $usage = '/texttobinary {reply_to|text}';

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
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $param = $message->getText(true);
        if ($param == '') {
            $replyTo = $message->getReplyToMessage();
            if (!$replyTo) {
                $data['text'] .= "<b>Error</b>: You should reply to a message or provide a text for using this command.\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
            $param = $replyTo->getText();
        }
        if ($param == '') {
            $data['text'] .= "<b>Error</b>: You should reply to a message or provide a text for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        if (strlen($param) < 1) {
            $data['text'] .= "<b>Error</b>: Text is too short.\n";
            $data['text'] .= "The minimum length is 1 characters.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        if (strlen($param) > 16) {
            $data['text'] .= "<b>Error</b>: Text is too long.\n";
            $data['text'] .= "The maximum length is 16 characters.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $data['parse_mode'] = '';
        $data['text'] = strtoupper(bin2hex($param));

        $this->dispatch(new SendMessageJob($data));
    }
}
