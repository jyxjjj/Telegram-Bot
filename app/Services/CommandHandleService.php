<?php

namespace App\Services;

use App\Common\BotCommon;
use App\Jobs\SendMessageJob;
use App\Models\TStarted;
use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Throwable;

class CommandHandleService extends BaseService
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
        $senderId = $message->getFrom()->getId();
        $isStarted = TStarted::getUser($senderId);
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $notAdmin = !BotCommon::isAdmin($message);
        $notPrivate = !$message->getChat()->isPrivateChat();
        $sendCommand = $message->getCommand();
        $files = glob(app_path('Services/Commands/*Command.php'));
        foreach ($files as $file) {
            $fileName = $file->getFileName();
            $command = str_replace('.php', '', $fileName);
            $command_class = "App\\Services\\Commands\\$command";
            try {
                $command_class = app()->make($command_class);
            } catch (Throwable) {
                continue;
            }
            if ($command_class->name != $sendCommand) { // Detect if command matches
                continue;
            }
            if ($command_class->admin && $notAdmin) { // Detect if command is admin only
                $data = [
                    'chat_id' => $senderId,
                    'reply_to_message_id' => $messageId,
                    'text' => '',
                ];
                $data['text'] .= "This command is admin only.\n";
                !$isStarted && $data['chat_id'] = $chatId;
                !$isStarted && $data['text'] .= "You should send a message to me in private, so that i can send message to you.\n";
                $this->dispatch(new SendMessageJob($data));
                return true;
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
                $this->dispatch(new SendMessageJob($data));
                return true;
            }
            $command_class->execute($message, $telegram, $updateId); // Execute command
            return true;
        }
        return false;
    }
}
