<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id 主键
 * @property int $chat_id 聊天ID
 * @property string $software 软件名称
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property ?int $deleted_at 删除时间
 */
class TUpdateSubscribes extends BaseModel
{
    protected $table = 'update_subscribes';

    /**
     * @param int $chat_id
     * @param string $software
     * @return false|TUpdateSubscribes
     */
    public static function addSubscribe(int $chat_id, string $software): false|TUpdateSubscribes
    {
        $data = Cache::get("DB::TUpdateSubscribes::update_subscribes::$chat_id");
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
            $data = self::query()
                ->create([
                    'chat_id' => $chat_id,
                    'software' => $software,
                ]);
            Cache::forget("DB::TUpdateSubscribes::update_subscribes");
            Cache::forget("DB::TUpdateSubscribes::update_subscribes::$chat_id");
            return $data;
        }
        return false;
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
        Cache::put("DB::TUpdateSubscribes::update_subscribes", $data, Carbon::now()->addMinutes(5));
        return $data;
    }

    /**
     * @param int $chatId
     * @return array
     */
    public static function getAllSubscribeByChat(int $chatId): array
    {
        $data = Cache::get("DB::TUpdateSubscribes::update_subscribes::$chatId");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->where('chat_id', $chatId)
            ->get()
            ->toArray();
        Cache::put("DB::TUpdateSubscribes::update_subscribes::$chatId", $data, Carbon::now()->addMinutes(5));
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
     * @param int $chat_id
     * @return int
     */
    public static function removeAllSubscribe(int $chat_id): int
    {
        $int = self::query()
            ->where('chat_id', $chat_id)
            ->delete();
        Cache::forget("DB::TUpdateSubscribes::update_subscribes");
        Cache::forget("DB::TUpdateSubscribes::update_subscribes::$chat_id");
        return $int;
    }

    /**
     * @param int $chat_id
     * @param string $software
     * @return int
     */
    public static function removeSubscribe(int $chat_id, string $software): int
    {
        $int = self::query()
            ->where([
                'chat_id' => $chat_id,
                'software' => $software,
            ])
            ->delete();
        Cache::forget("DB::TUpdateSubscribes::update_subscribes");
        Cache::forget("DB::TUpdateSubscribes::update_subscribes::$chat_id");
        return $int;
    }
}
