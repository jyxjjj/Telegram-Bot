<?php

namespace App\Http\Services\Bots;

use App\Http\Services\BaseService;
use App\Http\Services\Bots\Jobs\BanChatMemberJob;
use App\Http\Services\Bots\Jobs\SendMessageJob;
use App\Http\Services\Bots\Plugins\BanUser;
use App\Http\Services\Bots\Plugins\UserNameChecker;
use App\Http\Services\Bots\Plugins\ZaiHuaBot;
use Longman\TelegramBot\Entities\ChatMemberUpdated;
use Longman\TelegramBot\Telegram;

class ChatMemberService extends BaseService
{
    /**
     * @param ChatMemberUpdated $chatMember
     * @param Telegram $telegram
     * @param int $updateId
     */
    public static function handle(ChatMemberUpdated $chatMember, Telegram $telegram, int $updateId)
    {
        $chatId = $chatMember->getChat()->getId();
        $newChatMember = $chatMember->getNewChatMember();
        $newChatMemberUser = $newChatMember->getUser();
        $newChatMemberId = $newChatMemberUser->getId();
        $newChatMemberName = $newChatMemberUser->getLastName() . $newChatMemberUser->getFirstName();
        $newChatMemberStatus = $newChatMember->getStatus() == 'member' ? 1 : 0;
        if ($newChatMemberStatus == 1) {
            if ($chatId == -1001091256481) {
                $data = [];
                ZaiHuaBot::newMemberMessage($data, $chatId, $newChatMemberName, $newChatMemberId);
                SendMessageJob::dispatch($data, null, 10);
            } else {
                $shouldBan = UserNameChecker::check($chatId, $newChatMemberName);
                if ($shouldBan) {
                    $data = [];
                    BanUser::banChatMember($data, $chatId, $newChatMemberId, now()->getTimestamp() + 31622400);
                    BanChatMemberJob::dispatch($data);
                }
            }
        }
    }
}
