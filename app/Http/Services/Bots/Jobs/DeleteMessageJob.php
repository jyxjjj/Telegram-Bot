<?php

namespace App\Http\Services\Bots\Jobs;

use App\Http\Services\Bots\BotCommon;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class DeleteMessageJob extends TelegramBaseQueue
{
    private array $data;

    public function __construct(array $data, int $delay = 0)
    {
        parent::__construct();
        $this->data = $data;
        $this->delay($delay);
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        $botCommon = new BotCommon;
        $botCommon->newTelegram();
        $serverResponse = Request::deleteMessage($this->data);
        if (!$serverResponse->isOk()) {
            $this->release(1);
        }
    }
}
