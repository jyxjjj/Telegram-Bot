<?php

namespace App\Common\Log;

use App\Common\Conversation;

class BL
{
    public static function get(int $user_id): bool
    {
        return in_array($user_id, Conversation::get('blacklist', 'blacklist'));
    }

    public static function add(int $user_id): bool
    {
        $data = Conversation::get('blacklist', 'blacklist');
        if (in_array($user_id, $data)) {
            return false;
        }
        $data[] = $user_id;
        return Conversation::save('blacklist', 'blacklist', $data);
    }

    public static function remove(int $user_id): bool
    {
        $data = Conversation::get('blacklist', 'blacklist');
        if (!in_array($user_id, $data)) {
            return false;
        }
        $data = array_values(array_diff($data, [$user_id]));
        return Conversation::save('blacklist', 'blacklist', $data);
    }
}
