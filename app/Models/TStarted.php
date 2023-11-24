<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $user_id
 */
class TStarted extends BaseModel
{
    protected $table = 'started';

    /**
     * @param $user_id
     * @return TStarted
     */
    public static function addUser($user_id): TStarted
    {
        $data = Cache::get("DB::TStarted::user::$user_id");
        if ($data) {
            return $data;
        }
        $data = self::query()
            ->select('user_id')
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            $data = self::query()
                ->create([
                    'user_id' => $user_id,
                ]);
            Cache::put("DB::TStarted::user::$user_id", $data, Carbon::now()->addMinutes(5));
        }
        return $data;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public static function getUser($user_id): bool
    {
        $data = Cache::get("DB::TStarted::user::$user_id");
        if ($data) {
            return true;
        }
        $data = self::query()
            ->where('user_id', $user_id)
            ->first();
        if ($data) {
            Cache::put("DB::TStarted::user::$user_id", $data, Carbon::now()->addMinutes(5));
            return true;
        }
        return false;
    }
}
