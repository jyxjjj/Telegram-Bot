<?php

namespace App\Common\Log;

use App\Common\Conversation;

class WL
{
    public static function get(int $user_id): bool
    {
        return in_array($user_id, Conversation::get('whitelist', 'whitelist'));
    }

    public static function add(int $user_id): bool
    {
        $data = Conversation::get('whitelist', 'whitelist');
        if (in_array($user_id, $data)) {
            return false;
        }
        $data[] = $user_id;
        return Conversation::save('whitelist', 'whitelist', $data);
    }

    public static function remove(int $user_id): bool
    {
        $data = Conversation::get('whitelist', 'whitelist');
        if (!in_array($user_id, $data)) {
            return false;
        }
        $data = array_diff($data, [$user_id]);
        return Conversation::save('whitelist', 'whitelist', $data);
    }
}
