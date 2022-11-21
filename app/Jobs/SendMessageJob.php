<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendMessageJob extends TelegramBaseQueue
{
    private array $data;
    private ?array $extras;
    private int $delete;

    /**
     * @param array $data
     * @param array|null $extras
     * @param int $delete
     */
    public function __construct(array $data, ?array $extras = null, int $delete = 60)
    {
        parent::__construct();
        $this->data = array_merge([
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ], $data);
        $this->extras = $extras;
        $this->delete = $delete;
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
            $data = [
                'chat_id' => $this->data['chat_id'],
                'message_id' => $messageId,
            ];
            if ($this->delete !== 0) {
                DeleteMessageJob::dispatch($data, $this->delete);
            }
        } else {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Forbidden: bot was blocked by the user'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
