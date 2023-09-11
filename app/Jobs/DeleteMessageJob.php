<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class DeleteMessageJob extends BaseQueue
{
    private array $data;

    /**
     * @param array $data
     * @param int $delay
     */
    public function __construct(array $data, int $delay = 60)
    {
        parent::__construct();
        $this->data = $data;
        $this->delay($delay);
    }

    /**
     * @throws TelegramException
     */
    public function handle(): void
    {
        BotCommon::getTelegram();
        $serverResponse = Request::deleteMessage($this->data);
        if (!$serverResponse->isOk()) {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Bad Request: message to delete not found' &&
                $errorDescription != 'Bad Request: message can\'t be deleted' &&
                $errorDescription != 'Forbidden: bot was kicked from the supergroup chat'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
