<?php

namespace App\Services\Callbacks;

use App\Jobs\AnswerCallbackQueryJob;
use App\Services\Base\BaseCallback;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Telegram;

class PendingCallback extends BaseCallback
{
    /**
     * @param CallbackQuery $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function handle(CallbackQuery $message, Telegram $telegram, int $updateId): void
    {
        Log::debug($message);
        $data = [
            'callback_query_id' => $message->getId(),
            'text' => '测试成功',
            'show_alert' => true,
        ];
        $this->dispatch(new AnswerCallbackQueryJob($data));
    }
}
