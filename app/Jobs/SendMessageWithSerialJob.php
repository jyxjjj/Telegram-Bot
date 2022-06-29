<?php

namespace App\Jobs;

use App\Common\BotCommon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendMessageWithSerialJob extends TelegramBaseQueue
{
    private array $data;
    private string $serial;
    private ?array $extras;

    /**
     * @param array $data
     * @param string $serial
     * @param array|null $extras
     */
    public function __construct(array $data, string $serial, ?array $extras = null)
    {
        parent::__construct();
        $this->data = $data;
        $this->serial = $serial;
        $this->extras = $extras;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        BotCommon::getTelegram();
        $serverResponse = Request::sendMessage($this->data, $this->extras);
        if ($serverResponse->isOk()) {
            /** @var Message $sendResult */
            $sendResult = $serverResponse->getResult();
            $messageId = $sendResult->getMessageId();
            Cache::put('SendMessageSerial_' . $this->serial, $messageId, Carbon::now()->addMinutes(5));
        } else {
            $this->release(1);
        }
    }
}
