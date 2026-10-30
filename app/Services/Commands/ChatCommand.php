<?php

namespace App\Services\Commands;

use App\Jobs\RequestModelJob;
use App\Jobs\SendMessageJob;
use App\Jobs\SendMessageWithKeyJob;
use App\Services\Base\BaseCommand;
use DESMG\RFC6986\Hash;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ChatCommand extends BaseCommand
{
    public string $name = 'chat';
    public string $description = 'Chat with an AI model';
    public string $usage = '/chat {text}';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        if (!in_array($message->getChat()->getId(), [-4971290320, -1001391154172], true)) {
            return;
        }
        $data = [
            'chat_id' => $message->getChat()->getId(),
            'reply_to_message_id' => $message->getMessageId(),
            'parse_mode' => '',
        ];
        $prompt = $message->getText(true);
        if ($prompt === '') {
            $data['text'] = 'Usage: /chat {text}';
            SendMessageJob::dispatch($data);
            return;
        }
        if (mb_strlen($prompt) > 128) {
            $data['text'] = '输入不能超过128个字符';
            SendMessageJob::dispatch($data);
            return;
        }
        if (!config('openai.api_key') || !config('openai.model')) {
            $data['text'] = 'API key or model not configured.';
            SendMessageJob::dispatch($data);
            return;
        }
        $key = "AI_Chat_Message_{$data['chat_id']}_$updateId";
        $data['text'] = '正在思考';
        SendMessageWithKeyJob::dispatch($data, $key)->onQueue('model-messages');
        RequestModelJob::dispatch($data, $prompt, $key, Hash::sha256((string) $message->getFrom()->getId()));
    }
}
