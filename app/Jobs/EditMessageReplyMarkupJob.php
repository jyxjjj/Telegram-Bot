<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class EditMessageReplyMarkupJob extends BaseQueue
{
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        BotCommon::getTelegram();
        $serverResponse = Request::editMessageReplyMarkup($this->data);
        if (!$serverResponse->isOk()) {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
            $this->release(1);
        }
    }
}