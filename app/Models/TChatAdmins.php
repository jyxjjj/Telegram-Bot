<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $admin_id
 */
class TChatAdmins extends BaseModel
{
    protected $table = 'chat_admins';

    /**
     * @param $chat_id
     * @return array
     */
    public static function getChatAdmins($chat_id): array
    {
        return
            self::query()
                ->where('chat_id', $chat_id)
                ->pluck('admin_id')
                ->toArray();
    }

    /**
     * @param $chat_id
     * @return int
     */
    public static function clearAdmin($chat_id): int
    {
        return
            self::query()
                ->where('chat_id', $chat_id)
                ->delete();
    }

    /**
     * @param $chat_id
     * @param $admin_id
     * @return Builder|Model
     */
    public static function addAdmin($chat_id, $admin_id): Builder|Model
    {
        return
            self::query()
                ->create(
                    [
                        'chat_id' => $chat_id,
                        'admin_id' => $admin_id,
                    ]
                );
    }
}
