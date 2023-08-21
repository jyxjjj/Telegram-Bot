<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Common\Conversation;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SendPhotoJob extends BaseQueue
{
    private array $data;
    private int $delete;
    private string|bool $needSave;

    /**
     * @param array $data
     * @param int $delete
     */
    public function __construct(array $data, int $delete = 60)
    {
        parent::__construct();
        if (isset($data['need_save'])) {
            $this->needSave = $data['need_save'];
            unset($data['need_save']);
        } else {
            $this->needSave = false;
        }
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
        $serverResponse = Request::sendPhoto($this->data);
        if ($serverResponse->isOk()) {
                /** @var Message $sendResult */
                $sendResult = $serverResponse->getResult();
                $messageId = $sendResult->getMessageId();
                if ($this->needSave) {
                    $pendingData = Conversation::get('messagelink', 'pending');
                    $pendingData[$this->needSave] = $messageId;
                    Conversation::save('messagelink', 'pending', $pendingData);
                }
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
                $data = [
                    'chat_id' => env('YPP_SOURCE_ID'),
                    'text' => '',
                ];
                $data['text'] .= "A Message with a photo sent failed, please check the log.\n";
                SendMessageJob::dispatch($data);
                $this->release(1);
            }
        }
    }
}
