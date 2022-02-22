<?php

namespace App\Http\Services\Bots;

use App\Http\Services\BaseService;
use Longman\TelegramBot\Entities\ChatMemberUpdated;
use Longman\TelegramBot\Telegram;

class MyChatMemberService extends BaseService
{
    public static function handle(ChatMemberUpdated $chatMember, Telegram $telegram, int $updateId)
    {

    }
}
