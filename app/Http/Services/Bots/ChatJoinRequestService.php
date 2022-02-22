<?php

namespace App\Http\Services\Bots;

use App\Http\Services\BaseService;
use Longman\TelegramBot\Entities\ChatJoinRequest;
use Longman\TelegramBot\Telegram;

class ChatJoinRequestService extends BaseService
{
    public static function handle(ChatJoinRequest $chatMember, Telegram $telegram, int $updateId)
    {

    }
}
