<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendMessageWithKeyJob extends TelegramBaseQueue
{
    private array $data;
    private string $key;
    private ?array $extras;

    /**
     * @param array $data
     * @param string $key
     * @param array|null $extras
     */
    public function __construct(array $data, string $key, ?array $extras = null)
    {
        parent::__construct();
        $this->data = array_merge($data, [
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        $this->key = $key;
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
            $messageId = BotCommon::getMessageId($sendResult);
            Cache::put($this->key, $messageId, Carbon::now()->addMinutes(5));
        } else {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
            $this->release(1);
        }
    }
}
