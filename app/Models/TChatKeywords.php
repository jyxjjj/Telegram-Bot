<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id 主键
 * @property int $chat_id 聊天ID
 * @property string $keyword 关键字
 * @property string $target 检测目标
 * @property string $operation 执行操作
 * @property string $data 操作数据
 * @property int $enable 启用
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property ?int $deleted_at 删除时间
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
     * @return Collection<TChatKeywords>
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
