<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $chat_id
 * @property int $mid
 */
class TBilibiliSubscribes extends BaseModel
{
    protected $table = 'bilibili_subscribes';

    /**
     * @param int $chat_id
     * @param int $mid
     * @return Builder|Model|false
     */
    public static function addSubscribe(int $chat_id, int $mid): Builder|Model|false
    {
        $data = self::query()
            ->where([
                'chat_id' => $chat_id,
                'mid' => $mid,
            ])
            ->first();
        if ($data == null) {
            return self::query()
                ->create([
                    'chat_id' => $chat_id,
                    'mid' => $mid,
                ]);
        }
        return false;
    }

    /**
     * @param int $chat_id
     * @return mixed
     */
    public static function removeAllSubscribe(int $chat_id): int
    {
        return self::query()
            ->where('chat_id', $chat_id)
            ->delete();
    }

    /**
     * @param int $chat_id
     * @param int $mid
     * @return int
     */
    public static function removeSubscribe(int $chat_id, int $mid): int
    {
        return self::query()
            ->where([
                'chat_id' => $chat_id,
                'mid' => $mid,
            ])
            ->delete();
    }

    /**
     * @return Collection
     */
    public static function getAllSubscribe(): Collection
    {
        return self::query()
            ->get();
    }
}
