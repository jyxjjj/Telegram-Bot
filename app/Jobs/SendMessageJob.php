<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendMessageJob extends BaseQueue
{
    private array $data;
    private ?array $extras;
    private int $delete;

    /**
     * @param array      $data
     * @param array|null $extras
     * @param int        $delete
     */
    public function __construct(array $data, ?array $extras = null, int $delete = 60)
    {
        parent::__construct();
        $this->data = array_merge([
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ], $data);
        $this->extras = $extras;
        $this->delete = $delete;
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
                $errorDescription != 'Forbidden: bot was blocked by the user' &&
                $errorDescription != 'Forbidden: bot can\'t initiate conversation with a user' &&
                $errorDescription != 'Forbidden: bot was kicked from the supergroup chat' &&
                $errorDescription != 'Forbidden: bot was kicked from the group chat'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
