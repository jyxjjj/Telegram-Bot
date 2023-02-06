<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendPhotoJob extends TelegramBaseQueue
{
    private array $data;
    private int $delete;

    /**
     * @param array $data
     * @param int $delete
     */
    public function __construct(array $data, int $delete = 60)
    {
        parent::__construct();
        $this->data = array_merge($data, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        $this->delete = $delete;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        BotCommon::getTelegram();
        if (isset($this->data['is_file']) && $this->data['is_file']) {
            $this->data['photo'] = Request::encodeFile($this->data['photo']);
            unset($this->data['is_file']);
        }
        $serverResponse = Request::sendPhoto($this->data);
        if ($serverResponse->isOk()) {
            if ($this->delete !== 0) {
                /** @var Message $sendResult */
                $sendResult = $serverResponse->getResult();
                $messageId = $sendResult->getMessageId();
                $data = [
                    'chat_id' => $this->data['chat_id'],
                    'message_id' => $messageId,
                ];
                DeleteMessageJob::dispatch($data, $this->delete);
            }
        } else {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Forbidden: bot was blocked by the user' ||
                $errorDescription != 'Forbidden: bot can\'t initiate conversation with a user' ||
                $errorDescription != 'Forbidden: bot was kicked from the supergroup chat'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
                $this->release(1);
            }
        }
    }
}
