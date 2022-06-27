<?php

namespace App\Http\Services;

use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class MessageHandleService extends BaseService
{
    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     */
    public static function handle(Message $message, Telegram $telegram, int $updateId)
    {
        $messageType = $message->getType();
        switch ($messageType) {
            case 'command':
                CommandHandleService::handle($message, $telegram, $updateId);
                break;
            case 'text':
                TextMessageHandleService::handle($message, $telegram, $updateId);
                break;
//            case 'audio':
//            case 'animation':
//            case 'document':
//            case 'game':
//            case 'photo':
//            case 'sticker':
//            case 'video':
//            case 'voice':
//            case 'video_note':
//            case 'contact':
//            case 'location':
//            case 'venue':
//            case 'poll':
//            case 'new_chat_members':
//                NewChatMembersService::handle($message, $telegram, $updateId);
//                break;
//            case 'left_chat_member':
//                break;
//            case 'new_chat_title':
//            case 'new_chat_photo':
//            case 'delete_chat_photo':
//            case 'group_chat_created':
//            case 'supergroup_chat_created':
//            case 'channel_chat_created':
//            case 'message_auto_delete_timer_changed':
//            case 'migrate_to_chat_id':
//            case 'migrate_from_chat_id':
//            case 'pinned_message':
//            case 'invoice':
//            case 'successful_payment':
//            case 'passport_data':
//            case 'proximity_alert_triggered':
//            case 'voice_chat_scheduled':
//            case 'voice_chat_started':
//            case 'voice_chat_ended':
//            case 'voice_chat_participants_invited':
//            case 'reply_markup':
//                break;
            default:
                break;
        }
    }
}
