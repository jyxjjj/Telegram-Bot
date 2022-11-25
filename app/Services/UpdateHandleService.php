<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class UpdateHandleService extends BaseService
{
    /**
     * @var array
     */
    private array $handlers = [];

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     * @throws BindingResolutionException
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
//                 任何类型的新传入消息--文本、照片、贴纸等。
        $this->addHandler(Update::TYPE_MESSAGE, MessageHandleService::class);
//                 机器人已知并已编辑的消息的新版本。
//        $this->addHandler(Update::TYPE_EDITED_MESSAGE, EditedMessageHandleService::class);
//                 任何类型的新传入频道帖子--文字、照片、贴纸等。
        $this->addHandler(Update::TYPE_CHANNEL_POST, ChannelPostHandleService::class);
//                 机器人已知并已编辑的频道帖子的新版本。
//        $this->addHandler(Update::TYPE_EDITED_CHANNEL_POST, MessageHandleService::class);
//                 新传入的内联(https://core.telegram.org/bots/api#inline-mode)查询。
//        $this->addHandler(Update::TYPE_INLINE_QUERY, MessageHandleService::class);
//                 用户选择并发送给他们的聊天伙伴的内联(https://core.telegram.org/bots/api#inline-mode)查询的结果。
//                 有关如何为您的机器人启用这些更新的详细信息，请参阅我们关于收集反馈(https://core.telegram.org/bots/inline#collecting-feedback)的文档。
//        $this->addHandler(Update::TYPE_CHOSEN_INLINE_RESULT, MessageHandleService::class);
//                 新的传入回调查询。
//        $this->addHandler(Update::TYPE_CALLBACK_QUERY, MessageHandleService::class);
//                 新运费查询。仅适用于价格灵活的发票。
//        $this->addHandler(Update::TYPE_SHIPPING_QUERY, MessageHandleService::class);
//                 新传入的预结帐查询。包含有关结帐的完整信息。
//        $this->addHandler(Update::TYPE_PRE_CHECKOUT_QUERY, MessageHandleService::class);
//                 新的投票状态。机器人仅接收由机器人发送的有关已停止的投票和投票的更新。
//        $this->addHandler(Update::TYPE_POLL, MessageHandleService::class);
//                 一位用户在非匿名投票中更改了他们的答案。机器人仅在机器人本身发送的投票中获得新答案。
//        $this->addHandler(Update::TYPE_POLL_ANSWER, MessageHandleService::class);
//                 聊天机器人的聊天成员状态已在聊天中更新。对于私人聊天，仅当机器人被用户阻止或解除阻止时才会收到此更新。
//        $this->addHandler(Update::TYPE_MY_CHAT_MEMBER, MessageHandleService::class);
//                 聊天成员的状态已在聊天中更新。机器人必须是聊天中的管理员，并且必须在'allowed_updates'列表中明确指定'chat_member'才能接收这些更新。
        $this->addHandler(Update::TYPE_CHAT_MEMBER, ChatMemberHandleService::class);
//                 已发送加入聊天的请求。机器人必须在聊天中拥有can_invite_users管理员权限才能接收这些更新。
//        $this->addHandler(Update::TYPE_CHAT_JOIN_REQUEST, MessageHandleService::class);
        $updateType = $update->getUpdateType();
        $this->runHandler($updateType, $update, $telegram, $updateId);
    }

    /**
     * @param string $needType
     * @param string $class
     */
    private function addHandler(string $needType, string $class)
    {
        $this->handlers[] = [
            'type' => $needType,
            'class' => $class,
        ];
    }

    /**
     * @param string $type
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws BindingResolutionException
     * @throws TelegramException
     */
    private function runHandler(string $type, Update $update, Telegram $telegram, int $updateId): void
    {
        foreach ($this->handlers as $handler) {
            if ($type == $handler['type'] || $handler['type'] == '*' || $handler['type'] == 'ANY') {
                app()->make($handler['class'])->handle($update, $telegram, $updateId);
            }
        }
    }
}
