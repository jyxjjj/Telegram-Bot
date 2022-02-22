<?php

namespace App\Http\Services\Bots;

use App\Http\Services\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class NewChatMembersService extends BaseService
{
    public static function handle(Message $message, Telegram $telegram, int $updateId)
    {
        $messageId = $message->getMessageId();
        $from = $message->getFrom();
        $fromId = $from->getId();
        $fromUser = $from->getUsername();
        $fromSender = $from->getLastName() . $from->getFirstName();
        $chatId = $message->getChat()->getId();
        $newChatMembers = $message->getNewChatMembers();
        foreach ($newChatMembers as $newChatMember) {
            $newChatMemberId = $newChatMember->getId();
            $newChatMemberName = $newChatMember->getLastName() . $newChatMember->getFirstName();
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
                'allow_sending_without_reply' => true,
            ];
//            $shouldSend = false;
//            if ($chatId == -1001091256481) {
//                ZaiHuaBot::newMemberMessage($data, $newChatMemberName, $newChatMemberId);
//                $shouldSend = true;
//            }
//            $messageId = Request::sendMessage($data)->getResult()->getMessageId();
//            sleep(1);
//            Request::deleteMessage([
//                'chat_id' => $chatId,
//                'message_id' => $messageId,
//            ]);
        }
    }
}
