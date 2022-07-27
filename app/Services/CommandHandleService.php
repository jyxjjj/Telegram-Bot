<?php

namespace App\Services;

use App\Common\BotCommon;
use App\Jobs\SendMessageJob;
use App\Models\TStarted;
use Illuminate\Contracts\Bus\Dispatcher;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
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
     * @throws TelegramException
     */
    public static function handle(Message $message, Telegram $telegram, int $updateId): void
    {
        $senderId = BotCommon::getSender($message);
        $isStarted = TStarted::getUser($senderId);
        $messageId = BotCommon::getMessageId($message);
        $chatId = BotCommon::getChatId($message);
        $notAdmin = !BotCommon::isAdmin($message);
        $notPrivate = !BotCommon::isPrivateChat($message);
        $sendCommand = $message->getCommand();
        $path = app_path('Services/Commands');
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
            $command_class = "App\\Services\\Commands\\$command";
            require_once $pathName;
            if (!class_exists($command_class, false)) {
                continue;
            }
            $command_class = new $command_class; // instantiate the command
            if ($command_class->name != $sendCommand) { // Detect if command matches
                continue;
            }
            if ($command_class->admin && $notAdmin) {// Detect if command is admin only
                $data = [
                    'chat_id' => $senderId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command is admin only.\n";
                !$isStarted && $data['chat_id'] = $chatId;
                !$isStarted && $data['text'] .= "You should send a message to me in private, so that i can send message to you.\n";
                app(Dispatcher::class)->dispatch(new SendMessageJob($data));
                return;
            }
            if ($command_class->private && $notPrivate) {// Detect if command is private only
                $data = [
                    'chat_id' => $senderId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command needs to be sent in a private chat.\n";
                !$isStarted && $data['chat_id'] = $chatId;
                !$isStarted && $data['text'] .= "You should send a message to me in private, so that i can send message to you.\n";
                app(Dispatcher::class)->dispatch(new SendMessageJob($data));
                return;
            }
            $command_class->execute($message, $telegram, $updateId); // Execute command
            return;
        }
    }
}
