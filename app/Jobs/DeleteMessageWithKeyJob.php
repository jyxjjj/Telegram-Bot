<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class DeleteMessageWithKeyJob extends BaseQueue
{
    private array $data;
    private string $key;

    /**
     * @param array $data
     * @param string $key
     * @param int $delay
     */
    public function __construct(array $data, string $key, int $delay = 60)
    {
        parent::__construct();
        $this->data = $data;
        $this->key = $key;
        $this->delay($delay);
    }

    /**
     * @throws TelegramException
     */
    public function handle(): void
    {
        BotCommon::getTelegram();
        $messageId = Cache::get($this->key);
        if ($messageId) {
            $this->data['message_id'] = $messageId;
            $serverResponse = Request::deleteMessage($this->data);
            if (!$serverResponse->isOk()) {
                $errorCode = $serverResponse->getErrorCode();
                $errorDescription = $serverResponse->getDescription();
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
