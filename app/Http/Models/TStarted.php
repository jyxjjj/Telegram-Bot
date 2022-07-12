<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
        return self::query()->firstOrCreate(
            [
                'user_id' => $user_id,
            ],
            [
                'user_id' => $user_id,
            ]
        );
    }
}
