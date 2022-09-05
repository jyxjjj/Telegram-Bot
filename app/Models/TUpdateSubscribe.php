<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TUpdateSubscribe extends BaseModel
{
    protected $table = 'update_subscribe';

    /**
     * @param int $chat_id
     * @param string $software
     * @return Builder|Model|false
     */
    public static function addSubscribe(int $chat_id, string $software): Builder|Model|false
    {
        $data = self::query()
            ->where([
                'chat_id' => $chat_id,
                'software' => $software,
            ])
            ->first();
        if ($data == null) {
            return self::query()
                ->create([
                    'chat_id' => $chat_id,
                    'software' => $software,
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
     * @param string $software
     * @return int
     */
    public static function removeSubscribe(int $chat_id, string $software): int
    {
        return self::query()
            ->where([
                'chat_id' => $chat_id,
                'software' => $software,
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

    /**
     * @param int $chatId
     * @return Collection
     */
    public static function getAllSubscribeByChat(int $chatId): Collection
    {
        return self::query()
            ->where('chat_id', $chatId)
            ->get();
    }
}
