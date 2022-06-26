<?php

namespace App\Http\Services;

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class UpdateHandleService extends BaseService
{
    /**
     * @param Update $update
     * @param Telegram $telegram
     * @throws TelegramException
     */
    public static function handle(Update $update, Telegram $telegram)
    {
        $updateId = $update->getUpdateId();
        $updateType = $update->getUpdateType();
        switch ($updateType) {
            case Update::TYPE_MESSAGE:
                // 任何类型的新传入消息--文本、照片、贴纸等。
                MessageHandleService::handle($update->getMessage(), $telegram, $updateId);
                break;
            case Update::TYPE_EDITED_MESSAGE:
//                MessageHandleService::handle($update->getEditedMessage(), $telegram, $updateId);
                // 机器人已知并已编辑的消息的新版本。
                break;
            case Update::TYPE_CHANNEL_POST:
//                MessageHandleService::handle($update->getChannelPost(), $telegram, $updateId);
                // 任何类型的新传入频道帖子--文字、照片、贴纸等。
                break;
            case Update::TYPE_EDITED_CHANNEL_POST:
//                MessageHandleService::handle($update->getEditedChannelPost(), $telegram, $updateId);
                // 机器人已知并已编辑的频道帖子的新版本。
                break;
            case Update::TYPE_INLINE_QUERY:
                // 新传入的内联(https://core.telegram.org/bots/api#inline-mode)查询。
                break;
            case Update::TYPE_CHOSEN_INLINE_RESULT:
                // 用户选择并发送给他们的聊天伙伴的内联(https://core.telegram.org/bots/api#inline-mode)查询的结果。
                // 有关如何为您的机器人启用这些更新的详细信息，请参阅我们关于收集反馈(https://core.telegram.org/bots/inline#collecting-feedback)的文档。
                break;
            case Update::TYPE_CALLBACK_QUERY:
                // 新的传入回调查询。
                break;
            case Update::TYPE_SHIPPING_QUERY:
                // 新运费查询。仅适用于价格灵活的发票。
                break;
            case Update::TYPE_PRE_CHECKOUT_QUERY:
                // 新传入的预结帐查询。包含有关结帐的完整信息。
                break;
            case Update::TYPE_POLL:
                // 新的投票状态。机器人仅接收由机器人发送的有关已停止的投票和投票的更新。
                break;
            case Update::TYPE_POLL_ANSWER:
                // 一位用户在非匿名投票中更改了他们的答案。机器人仅在机器人本身发送的投票中获得新答案。
                break;
            case Update::TYPE_MY_CHAT_MEMBER:
                // 聊天机器人的聊天成员状态已在聊天中更新。对于私人聊天，仅当机器人被用户阻止或解除阻止时才会收到此更新。
//                MyChatMemberService::handle($update->getMyChatMember(), $telegram, $updateId);
                break;
            case Update::TYPE_CHAT_MEMBER:
                // 聊天成员的状态已在聊天中更新。机器人必须是聊天中的管理员，并且必须在'allowed_updates'列表中明确指定'chat_member'才能接收这些更新。
//                ChatMemberService::handle($update->getChatMember(), $telegram, $updateId);
                break;
            case Update::TYPE_CHAT_JOIN_REQUEST:
                // 已发送加入聊天的请求。机器人必须在聊天中拥有can_invite_users管理员权限才能接收这些更新。
//                ChatJoinRequestService::handle($update->getChatJoinRequest(), $telegram, $updateId);
                break;
            default:
                break;

        }
    }
}
