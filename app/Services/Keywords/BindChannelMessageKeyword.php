<?php

namespace App\Services\Keywords;

use App\Common\AllowedChats;
use App\Models\TChatHistoryOfBindChannel;
use App\Services\Base\BaseKeyword;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BindChannelMessageKeyword extends BaseKeyword
{
    public function preExecute(Message $message): bool
    {
        $chat = $message->getChat();
        if ($chat->isChannel()) {
            $chat_id = $chat->getId();
            $channels = AllowedChats::getChannels();
            if (in_array($chat_id, $channels)) {
                return true;
            }
        }
        return false;
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $text = $message->getText() ?? $message->getCaption() ?? '';
        if (strlen($text) >= 4 && strlen($text) <= 4096) {
            $chat = $message->getChat();
            $channel_id = $chat->getId();
            $message_id = $message->getMessageId();
            TChatHistoryOfBindChannel::newMessage($channel_id, $message_id, $text);
        }
    }
}
