<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $channel_id
 * @property int $message_id
 * @property string $content
 */
class TChatHistoryOfBindChannel extends BaseModel
{
    protected $table = 'chat_history_of_bind_channel';

    public static function newMessage($channel_id, $message_id, $content): Builder|Model
    {
        return self::query()
            ->create(
                [
                    'channel_id' => $channel_id,
                    'message_id' => $message_id,
                    'content' => $content,
                ]
            );
    }

    public static function searchMessage($channel_id, $keyword): array
    {
        return self::query()
            ->select('message_id')
            ->where('channel_id', $channel_id)
            ->where('content', 'like', "%$keyword%")
            ->pluck('message_id')
            ->toArray();
    }
}
