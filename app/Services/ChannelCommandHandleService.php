<?php

namespace App\Services;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Throwable;

class ChannelCommandHandleService extends BaseService
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
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $sendCommand = $message->getCommand();
        $path = app_path('Services/ChannelCommands');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+Command.php$/'
        );
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $command = str_replace('.php', '', $fileName);
            $command_class = "App\\Services\\ChannelCommands\\$command";
            try {
                $command_class = app()->make($command_class);
            } catch (Throwable) {
                continue;
            }
            if ($command_class->name != $sendCommand) { // Detect if command matches
                continue;
            }
            if ($command_class->admin) {
                $data = [
                    'chat_id' => $chatId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command doesn't support channel.\n";
                $this->dispatch(new SendMessageJob($data));
                return true;
            }
            if ($command_class->private) {
                $data = [
                    'chat_id' => $chatId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command needs to be sent in a private chat.\n";
                $this->dispatch(new SendMessageJob($data));
                return true;
            }
            $command_class->execute($message, $telegram, $updateId); // Execute command
            return true;
        }
        return false;
    }
}
