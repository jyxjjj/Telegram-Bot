<?php

namespace App\Services;

use App\Common\BotCommon;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class KeywordHandleService extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     */
    public static function handle(Message $message, Telegram $telegram, int $updateId): void
    {
        $path = app_path('Services/Keywords');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Keyword.php$/'
        );
        $sendText = BotCommon::getText($message);
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $pathName = $file->getPathName();
            $handler = str_replace('.php', '', $fileName);
            $handler_class = "App\\Services\\Keywords\\$handler";
            require_once $pathName;
            if (!class_exists($handler_class, false)) {
                continue;
            }
            $handler_class = new $handler_class; // Instantiate the Handler
            $handler_class->preExecute($sendText) && $handler_class->execute($message, $telegram, $updateId);
        }
    }
}
