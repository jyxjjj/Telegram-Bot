<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property int $from_chat_id
 * @property string $keyword
 * @property string $detect_source
 * @property string $operation
 * @property array $data
 * @property int $enable
 */
class TChatKeywords extends BaseModel
{
    /**
     * SOURCE
     */
    const SOURCE_USERID = 'USERID';
    const SOURCE_NAME = 'NAME';
    const SOURCE_CHATID = 'CHATID';
    const SOURCE_TITLE = 'TITLE';
    const SOURCE_TEXT = 'TEXT';
    /**
     * OPERATION
     */
    const OPERATION_REPLY = 'REPLY';
    const OPERATION_DELETE = 'DELETE';
    const OPERATION_WARN = 'WARN';
    const OPERATION_BAN = 'BAN';
    const OPERATION_RESTRICT = 'RESTRICT';

    protected $table = 'chat_keywords';
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @param int $chat_id
     * @param string $type
     * @return array
     */
    public static function getKeywords(int $chat_id, string $type = self::SOURCE_TEXT): array
    {
        $data = Cache::get("DB::TChatKeywords::chat_keywords::{$chat_id}::{$type}");
        if (is_array($data)) {
            return $data;
        }
        $data = match ($type) {
            self::SOURCE_USERID => self::query()
                ->select('user_id', 'operation', 'data')
                ->where('chat_id', $chat_id)
                ->where('detect_source', $type)
                ->where('enable', 1)
                ->get()
                ->toArray(),
            self::SOURCE_CHATID => self::query()
                ->select('from_chat_id', 'operation', 'data')
                ->where('chat_id', $chat_id)
                ->where('detect_source', $type)
                ->where('enable', 1)
                ->get()
                ->toArray(),
            default => self::query()
                ->select('keyword', 'operation', 'data')
                ->where('chat_id', $chat_id)
                ->where('detect_source', $type)
                ->where('enable', 1)
                ->get()
                ->toArray(),
        };
        Cache::put("DB::TChatKeywords::chat_keywords::{$chat_id}::{$type}", $data, Carbon::now()->addMinutes(5));
        return $data;
    }
}
