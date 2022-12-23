<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 */
class TChatKeywordsWhiteLists extends BaseModel
{
    protected $table = 'chat_keywords_white_lists';

    /**
     * @param $chat_id
     * @return array
     */
    public static function getChatWhiteLists($chat_id): array
    {
        $data = Cache::get("DB::TChatKeywordsWhiteLists::chat_keywords_white_lists::$chat_id");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->select('user_id')
            ->where('chat_id', $chat_id)
            ->pluck('user_id')
            ->toArray();
        Cache::put("DB::TChatKeywordsWhiteLists::chat_keywords_white_lists::$chat_id", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
