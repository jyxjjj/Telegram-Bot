<?php

namespace App\Http\Services\Bots\Plugins;

class BanUser
{
    public static function banChatMember(array &$data, int|string $chatId, int $userId, int $until, bool $revoke_messages = true)
    {
        $data = [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $until,
            'revoke_messages' => $revoke_messages,
        ];
    }

    public static function unbanChatMember(array &$data, int|string $chatId, int $userId, bool $only_if_banned = true)
    {
        $data = [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'only_if_banned' => $only_if_banned,
        ];
    }

    public static function restrictChatMember()
    {

    }

    public static function promoteChatMember()
    {

    }
}
