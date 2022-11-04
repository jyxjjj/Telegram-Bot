<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $chat_id
 * @property string $keyword
 * @property string $target
 * @property string $operation
 * @property array $data
 * @property int $enable
 */
class TChatKeywords extends BaseModel
{
    protected $table = 'chat_keywords';
    protected $casts = [
        'target' => TChatKeywordsTargetEnum::class,
        'operation' => TChatKeywordsOperationEnum::class,
        'data' => 'array',
    ];

    /**
     * @param int $chat_id
     * @return array
     */
    public static function getKeywords(int $chat_id): array
    {
        $data = Cache::get("DB::TChatKeywords::chat_keywords::{$chat_id}");
        if (is_array($data)) {
            return $data;
        }
        $data = self::query()
            ->select('keyword', 'target', 'operation', 'data')
            ->where('chat_id', $chat_id)
            ->where('enable', 1)
            ->get()
            ->toArray();
        Cache::put("DB::TChatKeywords::chat_keywords::{$chat_id}", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
