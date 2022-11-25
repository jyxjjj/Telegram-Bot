<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property int $times
 */
class TChatWarns extends BaseModel
{
    protected $table = 'chat_warns';

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return int
     */
    public static function getUserWarns(int $chat_id, int $user_id): int
    {
        $times = Cache::get("DB::TChatWarns::user_warns::$chat_id::$user_id");
        if (is_int($times)) {
            return $times;
        }
        /** @var TChatWarns $data */
        $data = self::query()
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            $times = 0;
        } else {
            $times = $data->times;
        }
        Cache::put("DB::TChatWarns::user_warns::$chat_id::$user_id", $times, Carbon::now()->addMinutes(5));
        return $times;
    }

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return void
     */
    public static function addUserWarn(int $chat_id, int $user_id): void
    {
        /** @var TChatWarns $data */
        $data = self::query()
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            Cache::put("DB::TChatWarns::user_warns::$chat_id::$user_id", 1, Carbon::now()->addMinutes(5));
            self::query()
                ->create(
                    [
                        'chat_id' => $chat_id,
                        'user_id' => $user_id,
                        'times' => 1,
                    ]
                );
        } else {
            Cache::increment("DB::TChatWarns::user_warns::$chat_id::$user_id");
            $data->times++;
            $data->save();
        }
    }

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return void
     */
    public static function revokeUserWarn(int $chat_id, int $user_id): void
    {
        /** @var TChatWarns $data */
        $data = self::query()
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            return;
        }
        $data->times--;
        if ($data->times <= 0) {
            Cache::put("DB::TChatWarns::user_warns::$chat_id::$user_id", 0, Carbon::now()->addMinutes(5));
            $data->delete();
        } else {
            Cache::decrement("DB::TChatWarns::user_warns::$chat_id::$user_id");
            $data->save();
        }
    }

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return void
     */
    public static function clearUserWarn(int $chat_id, int $user_id): void
    {
        Cache::put("DB::TChatWarns::user_warns::$chat_id::$user_id", 0, Carbon::now()->addMinutes(5));
        self::query()
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->delete();
    }
}
