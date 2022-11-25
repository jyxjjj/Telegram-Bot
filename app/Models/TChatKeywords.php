<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $chat_id
 * @property string $keyword
 * @property TChatKeywordsTargetEnum $target
 * @property TChatKeywordsOperationEnum $operation
 * @property array $data
 * @property int $enable
 */
class TChatKeywords extends BaseModel
{
    protected $table = 'chat_keywords';
    protected $casts = [
        'data' => 'array',
        'target' => TChatKeywordsTargetEnum::class,
        'operation' => TChatKeywordsOperationEnum::class,
    ];

    /**
     * @param int $chat_id
     * @return Builder|Collection|mixed
     */
    public static function getKeywords(int $chat_id): mixed
    {
        $data = Cache::get("DB::TChatKeywords::chat_keywords::$chat_id");
        if ($data) {
            return $data;
        }
        $data = self::query()
            ->select('keyword', 'target', 'operation', 'data')
            ->where('chat_id', $chat_id)
            ->where('enable', 1)
            ->get();
        Cache::put("DB::TChatKeywords::chat_keywords::$chat_id", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
