<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

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
        $data = Cache::get("DB::TChatAdmins::chat_admins::{$chat_id}");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->where('chat_id', $chat_id)
            ->pluck('admin_id')
            ->toArray();
        Cache::put("DB::TChatAdmins::chat_admins::{$chat_id}", $data, Carbon::now()->addMinutes(5));
        return $data;
    }

    /**
     * @param $chat_id
     * @return int
     */
    public static function clearAdmin($chat_id): int
    {
        Cache::forget("DB::TChatAdmins::chat_admins::{$chat_id}");
        return self::query()
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
        return self::query()
            ->create(
                [
                    'chat_id' => $chat_id,
                    'admin_id' => $admin_id,
                ]
            );
    }
}
