<?php

namespace App\Services\Callbacks;

use App\Services\Base\BaseCallback;
use Exception;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Telegram;

class PendingCallback extends BaseCallback
{
    /**
     * @param CallbackQuery $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws Exception
     */
    public function handle(CallbackQuery $message, Telegram $telegram, int $updateId): void
    {
        $callbackQueryId = $message->getId();
        $data = [
            'callback_query_id' => $callbackQueryId,
            'text' => '',
            'show_alert' => false,
        ];
        $isSelfSent = $telegram->getBotId() === $message->getMessage()->getFrom()->getId();
        if (!$isSelfSent) {
            $data['text'] = '本Bot不会处理来自其他Bot或转发消息的回调请求';
            AnswerCallbackQueryJob($data);
        }
    }
}
