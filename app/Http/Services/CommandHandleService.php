<?php

namespace App\Http\Services;

use App\Jobs\SendMessageJob;
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
        $isAdmin = !$telegram->isAdmin($message->getFrom()->getId());
        $notPrivate = !$message->getChat()->isPrivateChat();
        $sendCommand = $message->getCommand();
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $path = app_path('Http/Services/Commands');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Command.php$/'
        );
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $pathName = $file->getPathName();
            $command = str_replace('.php', '', $fileName);
            $command_class = "App\\Http\\Services\\Commands\\$command";
            require_once $pathName;
            if (!class_exists($command_class, false)) {
                continue;
            }
            $command_class = new $command_class; // instantiate the command
            if ($command_class->name != $sendCommand) { // Detect if command matches
                continue;
            }
            if ($command_class->admin && $isAdmin) {// Detect if command is admin only
                $data = [
                    'chat_id' => $chatId,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true,
                    'allow_sending_without_reply' => true,
                    'reply_to_message_id' => $messageId,
                    'text' => 'This command is admin only',
                ];
                dispatch(new SendMessageJob($data));
                return;
            }
            if ($command_class->private && $notPrivate) {// Detect if command is private only
                $data = [
                    'chat_id' => $chatId,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true,
                    'allow_sending_without_reply' => true,
                    'reply_to_message_id' => $messageId,
                    'text' => 'This command needs to be sent in a private chat.',
                ];
                dispatch(new SendMessageJob($data));
                return;
            }
            $command_class->execute($message, $telegram, $updateId); // Execute command
            return;
        }
    }
}
