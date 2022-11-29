<?php

namespace App\Models;

/**
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property int $times
 */
class TChatWarns extends BaseModel
{
    protected $table = 'chat_warns';

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return int
     */
    public static function getUserWarns(int $chat_id, int $user_id): int
    {
        /** @var TChatWarns $data */
        $data = self::query()
            ->select('times')
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            $times = 0;
        } else {
            $times = $data->times;
        }
        return $times;
    }

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return void
     */
    public static function addUserWarn(int $chat_id, int $user_id): void
    {
        /** @var TChatWarns $data */
        $data = self::query()
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            self::query()
                ->create(
                    [
                        'chat_id' => $chat_id,
                        'user_id' => $user_id,
                        'times' => 1,
                    ]
                );
        } else {
            $data->times++;
            $data->save();
        }
    }

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return void
     */
    public static function revokeUserWarn(int $chat_id, int $user_id): void
    {
        /** @var TChatWarns $data */
        $data = self::query()
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->first();
        if ($data == null) {
            return;
        }
        $data->times--;
        $data->times <= 0 ? $data->delete() : $data->save();
    }

    /**
     * @param int $chat_id
     * @param int $user_id
     * @return void
     */
    public static function clearUserWarn(int $chat_id, int $user_id): void
    {
        self::query()
            ->where('chat_id', $chat_id)
            ->where('user_id', $user_id)
            ->delete();
    }
}
