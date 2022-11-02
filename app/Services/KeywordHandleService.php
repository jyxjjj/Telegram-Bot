<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
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
        $sendText = $message->getText(true) ?? $message->getCaption();
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(app_path('Services/Keywords'))
            ),
            '/^.+Keyword.php$/'
        );
        if ($sendText) {
            foreach ($files as $file) {
                $fileName = $file->getFileName();
                $handler = str_replace('.php', '', $fileName);
                $handler_class = "App\\Services\\Keywords\\$handler";
                try {
                    $handler_class = app()->make($handler_class);
                } catch (Throwable) {
                    continue;
                }
                $handler_class->preExecute($sendText) && $handler_class->execute($message, $telegram, $updateId);
            }
        }
        return false;
    }
}
