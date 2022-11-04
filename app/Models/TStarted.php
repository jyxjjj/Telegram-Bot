<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
     * @return Builder|Model
     */
    public static function addUser($user_id): Builder|Model
    {
        $data = Cache::get("DB::TStarted::user::{$user_id}");
        if ($data) {
            return $data;
        }
        return self::query()
            ->firstOrCreate(
                [
                    'user_id' => $user_id,
                ],
                [
                    'user_id' => $user_id,
                ]
            );
    }

    /**
     * @param $user_id
     * @return bool
     */
    public static function getUser($user_id): bool
    {
        $data = Cache::get("DB::TStarted::user::{$user_id}");
        if ($data) {
            return true;
        }
        $data = self::query()
            ->where(
                [
                    'user_id' => $user_id,
                ]
            )
            ->first();
        if ($data) {
            Cache::put("DB::TStarted::user::{$user_id}", $data, Carbon::now()->addMinutes(5));
            return true;
        }
        return false;
    }
}
