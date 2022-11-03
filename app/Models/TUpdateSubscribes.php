<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
        $data = Cache::get("DB::TUpdateSubscribes::update_subscribes::{$chat_id}");
        if (is_array($data)) {
            foreach ($data as $item) {
                if ($item['software'] === $software) {
                    return false;
                }
            }
        }
        $data = self::query()
            ->where([
                'chat_id' => $chat_id,
                'software' => $software,
            ])
            ->first();
        if ($data == null) {
            Cache::forget("DB::TUpdateSubscribes::update_subscribes");
            Cache::forget("DB::TUpdateSubscribes::update_subscribes::{$chat_id}");
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
        Cache::forget("DB::TUpdateSubscribes::update_subscribes");
        Cache::forget("DB::TUpdateSubscribes::update_subscribes::{$chat_id}");
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
        Cache::forget("DB::TUpdateSubscribes::update_subscribes");
        Cache::forget("DB::TUpdateSubscribes::update_subscribes::{$chat_id}");
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
        $data = Cache::get("DB::TUpdateSubscribes::update_subscribes");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->get()
            ->toArray();
        Cache::put("DB::TUpdateSubscribes::update_subscribes", $data, now()->addDay());
        return $data;
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
        $data = Cache::get("DB::TUpdateSubscribes::update_subscribes::{$chatId}");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->where('chat_id', $chatId)
            ->get()
            ->toArray();
        Cache::put("DB::TUpdateSubscribes::update_subscribes::{$chatId}", $data, now()->addDay());
        return $data;
    }
}
