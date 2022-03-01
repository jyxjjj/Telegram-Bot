<?php

namespace App\Http\Services\Bots\Jobs;

use App\Http\Services\Bots\BotCommon;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;

class SendPhotoJob extends TelegramBaseQueue
{
    private array $data;
    private int $delete;

    /**
     * @param array $data
     * @param int $delete
     */
    public function __construct(array $data, int $delete = 5)
    {
        parent::__construct();
        $this->data = $data;
        $this->delete = $delete;
    }

    public function handle()
    {
        new BotCommon;
        $serverResponse = Request::sendPhoto($this->data);
        if ($serverResponse->isOk()) {
            /** @var Message $sendResult */
            $sendResult = $serverResponse->getResult();
            $messageId = $sendResult->getMessageId();
            $data = [
                'chat_id' => $this->data['chat_id'],
                'message_id' => $messageId,
            ];
            if ($this->delete !== 0) {
                DeleteMessageJob::dispatch($data, $this->delete);
            }
        } else {
            $this->release(1);
        }
    }
}
