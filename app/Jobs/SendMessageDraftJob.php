<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request;

class SendMessageDraftJob extends BaseQueue
{
    public function __construct(private array $data, private string $timestampKey, private int $timestamp, private bool $finished = false)
    {
        $this->onQueue('model-messages');
    }

    public function handle(): void
    {
        if (!$this->finished && $this->timestamp < Cache::get($this->timestampKey, 0)) {
            return;
        }
        BotCommon::getTelegram();
        $method = 'sendMessageDraft';
        if ($this->finished) {
            $method = 'sendMessage';
            $this->data['disable_web_page_preview'] = true;
            $this->data['allow_sending_without_reply'] = true;
        }
        $response = json_decode(Request::execute($method, $this->data), true);
        if (empty($response['ok'])) {
            Log::error("Telegram $method request failed", [$response]);
        } else {
            Cache::put($this->timestampKey, $this->timestamp, 3600);
        }
    }
}
