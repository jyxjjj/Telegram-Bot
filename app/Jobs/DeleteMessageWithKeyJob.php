<?php

namespace App\Jobs;

use App\Common\BotCommon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class DeleteMessageWithKeyJob extends TelegramBaseQueue
{
    private array $data;
    private string $key;

    /**
     * @param array $data
     * @param string $key
     * @param int $delay
     */
    public function __construct(array $data, string $key, int $delay = 30)
    {
        parent::__construct();
        $this->data = $data;
        $this->key = $key;
        $this->delay($delay);
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
            $serverResponse = Request::deleteMessage($this->data);
            if (!$serverResponse->isOk()) {
                $this->release(1);
            }
        } else {
            $this->release(1);
        }
    }
}
