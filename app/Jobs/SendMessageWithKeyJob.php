<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendMessageWithKeyJob extends BaseQueue
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
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        $this->key = $key;
        $this->extras = $extras;
    }

    /**
     * @throws TelegramException
     */
    public function handle(): void
    {
        BotCommon::getTelegram();
        $serverResponse = Request::sendMessage($this->data, $this->extras);
        if ($serverResponse->isOk()) {
            /** @var Message $sendResult */
            $sendResult = $serverResponse->getResult();
            $messageId = $sendResult->getMessageId();
            Cache::put($this->key, $messageId, Carbon::now()->addMinutes(5));
        } else {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Forbidden: bot was blocked by the user' &&
                $errorDescription != 'Forbidden: bot can\'t initiate conversation with a user' &&
                $errorDescription != 'Forbidden: bot was kicked from the supergroup chat'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
