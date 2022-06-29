<?php

namespace App\Http\Services;

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class CommandHandleService extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public static function handle(Message $message, Telegram $telegram, int $updateId): void
    {
        $path = app_path('Http/Services/Commands');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Command.php$/'
        );
        foreach ($files as $file) {
            $command = str_replace('.php', '', $file->getFileName());
            $command_class = "App\\Http\\Services\\Commands\\$command";
            require_once $file->getPathName();
            if (class_exists($command_class, false)) {
                $command_class = new $command_class($message, $telegram, $updateId);
                if ($command_class->name == $message->getCommand()) {
                    $command_class->execute($message, $telegram, $updateId);
                    return;
                }
            }
        }
    }
}
