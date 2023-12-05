<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Throwable;

class KeywordHandleService extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return bool
     * @throws TelegramException
     */
    public function handle(Message $message, Telegram $telegram, int $updateId): bool
    {
        $files = glob(app_path('Services/Keywords/*Keyword.php'));
        foreach ($files as $fileName) {
            $handler = basename($fileName, '.php');
            $handler_class = "App\\Services\\Keywords\\$handler";
            try {
                $handler_class = app()->make($handler_class);
            } catch (Throwable) {
                continue;
            }
            $handler_class->preExecute($message) && $handler_class->execute($message, $telegram, $updateId);
        }
        return false;
    }
}
