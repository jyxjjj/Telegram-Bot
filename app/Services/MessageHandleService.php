<?php

namespace App\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class MessageHandleService extends BaseService
{
    /**
     * @var array
     */
    private array $handlers;

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     * @throws BindingResolutionException
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
        $message = $update->getMessage();
        $messageType = $message->getType();
        $this->addHandler('ANY', AutoDeleteHandler::class);
        $this->addHandler('command', CommandHandleService::class);
        $this->addHandler('text', KeywordHandleService::class);
        $this->runHandler($messageType, $message, $telegram, $updateId);
//            'command':
//            'text':
//            'audio':
//            'animation':
//            'document':
//            'game':
//            'photo':
//            'sticker':
//            'video':
//            'voice':
//            'video_note':
//            'contact':
//            'location':
//            'venue':
//            'poll':
//            'new_chat_members':
//            'left_chat_member':
//            'new_chat_title':
//            'new_chat_photo':
//            'delete_chat_photo':
//            'group_chat_created':
//            'supergroup_chat_created':
//            'channel_chat_created':
//            'message_auto_delete_timer_changed':
//            'migrate_to_chat_id':
//            'migrate_from_chat_id':
//            'pinned_message':
//            'invoice':
//            'successful_payment':
//            'passport_data':
//            'proximity_alert_triggered':
//            'voice_chat_scheduled':
//            'voice_chat_started':
//            'voice_chat_ended':
//            'voice_chat_participants_invited':
//            'reply_markup':
    }

    /**
     * @param string $needType
     * @param string $class
     */
    private function addHandler(string $needType, string $class)
    {
        $this->handlers[] = [
            'type' => $needType,
            'class' => $class,
        ];
    }

    /**
     * @param string $type
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws BindingResolutionException
     * @throws TelegramException
     */
    private function runHandler(string $type, Message $message, Telegram $telegram, int $updateId): void
    {
        foreach ($this->handlers as $handler) {
            if ($type == $handler['type'] || $handler['type'] == '*' || $handler['type'] == 'ANY') {
                $handled = app()->make($handler['class'])->handle($message, $telegram, $updateId);
                if ($handled) {
                    return;
                }
            }
        }
    }
}
