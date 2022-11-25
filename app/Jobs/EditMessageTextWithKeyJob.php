<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class EditMessageTextWithKeyJob extends TelegramBaseQueue
{
    private array $data;
    private string $key;

    /**
     * @param array $data
     * @param string $key
     */
    public function __construct(array $data, string $key)
    {
        parent::__construct();
        $this->data = array_merge($data, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        $this->key = $key;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        BotCommon::getTelegram();
        $messageId = Cache::get($this->key);
        if ($messageId) {
            $this->data['message_id'] = $messageId;
            $serverResponse = Request::editMessageText($this->data);
            if (!$serverResponse->isOk()) {
                $errorCode = $serverResponse->getErrorCode();
                $errorDescription = $serverResponse->getDescription();
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        } else {
            $this->release(1);
        }
    }
}
