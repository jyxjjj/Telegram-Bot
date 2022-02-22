<?php

namespace App\Http\Services\Bots\Plugins;

class UserNameChecker
{

    public static function check(int $chatId, string $newChatMemberName): bool
    {
        if (preg_match('/^(.*)()(.*)$/i', $newChatMemberName, $matches)) {
            return true;
        }
        return false;
    }
}
