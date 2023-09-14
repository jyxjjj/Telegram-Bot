<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;

class ChatMemberHandleService extends BaseService
{

    //context={"chatMember":{"Longman\\TelegramBot\\Entities\\ChatMemberUpdated":{"chat":{"id":-1001091256481,"title":"在花の科技花 闲聊🎗","username":"ZaihuaChat","type":"supergroup"},"from":{"id":5489183746,"is_bot":false,"first_name":"小号 - 机器人开发测试工具","username":"jyxjjj_us","language_code":"en"},"date":1669256314,"old_chat_member":{"user":{"id":5489183746,"is_bot":false,"first_name":"小号 - 机器人开发测试工具","username":"jyxjjj_us","language_code":"en"},"status":"left"},"new_chat_member":{"user":{"id":5489183746,"is_bot":false,"first_name":"小号 - 机器人开发测试工具","username":"jyxjjj_us","language_code":"en"},"status":"member"}}}}

    //context={"chatMember":{"Longman\\TelegramBot\\Entities\\ChatMemberUpdated":{"chat":{"id":-1001091256481,"title":"在花の科技花 闲聊🎗","username":"ZaihuaChat","type":"supergroup"},"from":{"id":208056682,"is_bot":true,"first_name":"🛠 Security ¹ 🛠","username":"GHSecurityBot"},"date":1669256314,"old_chat_member":{"user":{"id":5489183746,"is_bot":false,"first_name":"小号 - 机器人开发测试工具","username":"jyxjjj_us","language_code":"en"},"status":"member"},"new_chat_member":{"user":{"id":5489183746,"is_bot":false,"first_name":"小号 - 机器人开发测试工具","username":"jyxjjj_us","language_code":"en"},"status":"restricted","until_date":0,"can_send_messages":false,"can_send_media_messages":false,"can_send_polls":false,"can_send_other_messages":false,"can_add_web_page_previews":false,"can_change_info":false,"can_invite_users":false,"can_pin_messages":false,"can_manage_topics":false,"is_member":true}}}}

    //context={"chatMember":{"Longman\\TelegramBot\\Entities\\ChatMemberUpdated":{"chat":{"id":-1001091256481,"title":"在花の科技花 闲聊🎗","username":"ZaihuaChat","type":"supergroup"},"from":{"id":5090130895,"is_bot":true,"first_name":"🔫 ~biu~biu~biu","username":"ZaihuaGroupBot"},"date":1669256319,"old_chat_member":{"user":{"id":5489183746,"is_bot":false,"first_name":"小号 - 机器人开发测试工具","username":"jyxjjj_us","language_code":"en"},"status":"restricted","until_date":0,"can_send_messages":false,"can_send_media_messages":false,"can_send_polls":false,"can_send_other_messages":false,"can_add_web_page_previews":false,"can_change_info":false,"can_invite_users":false,"can_pin_messages":false,"can_manage_topics":false,"is_member":true},"new_chat_member":{"user":{"id":5489183746,"is_bot":false,"first_name":"小号 - 机器人开发测试工具","username":"jyxjjj_us","language_code":"en"},"status":"member"}}}}

    //context={"chatMember":{"Longman\\TelegramBot\\Entities\\ChatMemberUpdated":{"chat":{"id":-1001091256481,"title":"在花の科技花 闲聊🎗","username":"ZaihuaChat","type":"supergroup"},"from":{"id":5090130895,"is_bot":true,"first_name":"🔫 ~biu~biu~biu","username":"ZaihuaGroupBot"},"date":1669258396,"old_chat_member":{"user":{"id":5450323463,"is_bot":false,"first_name":"Yoyo"},"status":"member"},"new_chat_member":{"user":{"id":5450323463,"is_bot":false,"first_name":"Yoyo"},"status":"kicked","until_date":0}}}}

    //https://telegram.desmg.org/group_join_verify
    //?chat_id=-1001091256481
    //&user_id=uid
    //&id=uid
    //&first_name=name
    //&username=account
    //&photo_url=urldecode.jpg
    //&auth_date=ts(10)
    //&hash=64str
    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
        $botId = $telegram->getBotId();
        $chatMember = $update->getChatMember();
        $chat = $chatMember->getChat();
        $chatId = $chat->getId();
        if ($chatId != -1001154500568) {
            return;
        }
        $from = $chatMember->getFrom();
        $fromId = $from->getId();
        $user = $chatMember->getNewChatMember()->getUser();
        $userId = $user->getId();
        $userNick = ($user->getFirstName() ?? '') . ($user->getLastName() ?? '');
        $originStatus = $chatMember->getOldChatMember()->getStatus();
        $status = $chatMember->getNewChatMember()->getStatus();
        // status: left, kicked, creator, administrator, member, restricted
//        Log::debug($chatMember->toJson());

        // 用户可能从left状态变为member状态 需要处理
        // - 有可能是加群从left状态变为member状态
        // - 有可能是邀请从left状态变为member状态
        // 用户可能从kicked状态变为member状态 需要处理
        // - 有可能是加群从kicked状态变为member状态
        // - 有可能是邀请从kicked状态变为member状态

        // 用户可能从member状态变为left状态 暂时不需要处理
        // - 只有用户主动退出群组才会变为left状态
        // 用户可能从member状态变为kicked状态 暂时不需要处理
        // - 只有管理员主动踢出用户才会变为kicked状态

        // 用户可能从member状态变为administrator状态 不需要处理
        // - 只有管理员主动设置用户为管理员才会变为administrator状态
        // 用户可能从member状态变为creator状态 不需要处理
        // - 只有群主主动设置用户为群主才会变为creator状态
        // 用户可能从administrator状态变为member状态 不需要处理
        // - 只有管理员主动取消用户的管理员身份才会变为member状态
        // 用户可能从administrator状态变为creator状态 不需要处理
        // - 只有群主主动设置用户为群主才会变为creator状态

        // 用户可能从member状态变为restricted状态 不需要处理
        // - 只有管理员主动设置用户为受限用户才会变为restricted状态

        // 用户可能从left状态变为kicked状态 不需要处理
        // - 只有管理员主动在用户退出群组后踢出用户才会变为kicked状态
        // 用户可能从left状态变为restricted状态 不需要处理
        // - 只有管理员主动在用户退出群组后设置用户为受限用户才会变为restricted状态
        // 用户可能从kicked状态变为left状态 不需要处理
        // - 只有管理员在踢出用户后取消踢出才会变为left状态
        // 用户可能从kicked状态变为restricted状态 不需要处理
        // - 只有管理员在踢出用户后设置用户为受限用户才会变为restricted状态
        // 用户可能从restricted状态变为left状态 不需要处理
        // - 只有管理员在设置用户为受限用户后取消设置才会变为left状态
        // 用户可能从restricted状态变为kicked状态 不需要处理
        // - 只有管理员在设置用户为受限用户后踢出用户才会变为kicked状态

        // 先判断需要处理的状态
//        if ($status == 'member') {
//            if ($originStatus == 'left' || $originStatus == 'kicked') {
//                if ($fromId == $userId) {
//                    // 用户是自己加群的
//                }
//                if ($fromId != $userId) {
//                    // 用户是其他人邀请的
//                }
//            }
//        }
//
//        if ($fromId == $botId) {
//            return;
//        }
//        $sender = [
//            'chat_id' => $chatId,
//            'text' => '',
//        ];
//        $sender['text'] .= "欢迎 [{$userNick}](tg://user?id={$userId})\n";
//        $sender['text'] .= "这是一条测试验证消息，暂*请使用其他机器人发送的进群验证*信息进行验证。\n";
//        $sender['text'] .= "请不要点击下方验证按钮";
//        $sender['reply_markup'] = new InlineKeyboard([]);
//        $loginUrl = new LoginUrl([
//            'url' => "https://telegram.desmg.org/group_join_verify?chat_id={$chatId}&user_id={$userId}",
//        ]);
//        $button = new InlineKeyboardButton(['text' => '测试验证无需点击', 'login_url' => $loginUrl]);
//        $sender['reply_markup']->addRow($button);
//        $this->dispatch(new SendMessageJob($sender, null, 180));
    }
}
