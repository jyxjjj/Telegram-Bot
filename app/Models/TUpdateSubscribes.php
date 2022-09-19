<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $chat_id
 * @property string $software
 */
class TUpdateSubscribes extends BaseModel
{
    protected $table = 'update_subscribes';

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
     * @return array
     */
    public static function getAllSubscribe(): array
    {
        return self::query()
            ->get()
            ->toArray();
    }

    /**
     * @param string $software
     * @return array
     */
    public static function getAllSubscribeBySoftware(string $software): array
    {
        return self::query()
            ->where('software', $software)
            ->get()
            ->toArray();
    }

    /**
     * @param int $chatId
     * @return array
     */
    public static function getAllSubscribeByChat(int $chatId): array
    {
        return self::query()
            ->where('chat_id', $chatId)
            ->get()
            ->toArray();
    }
}
