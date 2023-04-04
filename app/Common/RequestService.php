<?php

namespace App\Common;

use App\Jobs\DeleteMessageJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class RequestService
{
    use DispatchesJobs;

    private ?Telegram $telegram = null;

    /**
     * @throws TelegramException
     */
    public function __construct()
    {
        $this->bootstrap();
        if ($this->telegram == null) {
            $this->telegram = BotCommon::getTelegram();
        }
    }

    /**
     * @return void
     */
    private function bootstrap(): void
    {
        app()->scoped(get_class($this), fn() => $this);
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return app(self::class);
    }


    /**
     * @return Telegram
     * @noinspection PhpUnused
     */
    public function getTelegram(): Telegram
    {
        return $this->telegram;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function answerCallbackQuery(array $data): bool
    {
        $serverResponse = Request::answerCallbackQuery($data);
        if (!$serverResponse->isOk()) {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Bad Request: query is too old and response timeout expired or query ID is invalid'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $data]);
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $data
     * @param int $delete
     * @return int
     */
    public function sendMessage(array $data, int $delete = 60): int
    {
        $data = array_merge($data, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        try {
            $serverResponse = Request::sendMessage($data);
        } catch (TelegramException) {
            return -1;
        }
        if ($serverResponse->isOk()) {
            /** @var Message $sendResult */
            $sendResult = $serverResponse->getResult();
            $messageId = $sendResult->getMessageId();
            if ($delete != 0) {
                $deleter = [
                    'chat_id' => $data['chat_id'],
                    'message_id' => $messageId,
                ];
                $this->dispatch(new DeleteMessageJob($deleter, $delete));
                if (isset($data['reply_to_message_id'])) {
                    $deleter['message_id'] = $data['reply_to_message_id'];
                    $this->dispatch(new DeleteMessageJob($deleter, $delete));
                }
            }
            return $messageId;
        } else {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Forbidden: bot was blocked by the user' &&
                $errorDescription != 'Forbidden: bot can\'t initiate conversation with a user' &&
                $errorDescription != 'Forbidden: bot was kicked from the supergroup chat'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $data]);
                return -1;
            }
            return 0;
        }
    }

    /**
     * @param array $data
     * @return bool
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function deleteMessage(array $data): bool
    {
        $data = array_merge($data, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        $serverResponse = Request::deleteMessage($data);
        if (!$serverResponse->isOk()) {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            if (
                $errorDescription != 'Bad Request: message to delete not found' &&
                $errorDescription != 'Forbidden: bot was kicked from the supergroup chat'
            ) {
                Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $data]);
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function editMessageText(array $data): bool
    {
        $data = array_merge($data, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ]);
        $serverResponse = Request::editMessageText($data);
        if (!$serverResponse->isOk()) {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $data]);
            return false;
        } else {
            return true;
        }
    }
}
