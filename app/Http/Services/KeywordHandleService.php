<?php

namespace App\Http\Services;

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
        $path = app_path('Http/Services/Keywords');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Keyword.php$/'
        );
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $pathName = $file->getPathName();
            $handler = str_replace('.php', '', $fileName);
            $handler_class = "App\\Http\\Services\\Keywords\\$handler";
            require_once $pathName;
            if (!class_exists($handler_class, false)) {
                continue;
            }
            $handler_class = new $handler_class; // instantiate the command
            if ($handler_class->pattern !== null && preg_match($handler_class->pattern, $message->getText())) {
                $handler_class->execute($message, $telegram, $updateId);
            }
            return;
        }
    }
}
