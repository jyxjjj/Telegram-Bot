<?php

namespace App\Models;

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
     * @param $admin_id
     * @return TChatAdmins
     */
    public static function addAdmin($chat_id, $admin_id): TChatAdmins
    {
        $data = self::query()
            ->create(
                [
                    'chat_id' => $chat_id,
                    'admin_id' => $admin_id,
                ]
            );
        Cache::forget("DB::TChatAdmins::chat_admins::$chat_id");
        return $data;
    }

    /**
     * @param $chat_id
     * @return void
     */
    public static function clearAdmin($chat_id): void
    {
        self::query()
            ->where('chat_id', $chat_id)
            ->delete();
        Cache::forget("DB::TChatAdmins::chat_admins::$chat_id");
    }

    /**
     * @param $chat_id
     * @return array
     */
    public static function getChatAdmins($chat_id): array
    {
        $data = Cache::get("DB::TChatAdmins::chat_admins::$chat_id");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->select('admin_id')
            ->where('chat_id', $chat_id)
            ->pluck('admin_id')
            ->toArray();
        $data = array_merge($data, [(int)env('TELEGRAM_ADMIN_USER_ID')]);
        Cache::put("DB::TChatAdmins::chat_admins::$chat_id", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
