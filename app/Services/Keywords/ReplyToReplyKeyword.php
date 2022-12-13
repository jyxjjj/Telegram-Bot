<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ReplyToReplyKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return (
                $message->getChat()->isGroupChat() ||
                $message->getChat()->isSuperGroup()
            ) &&
            $message->getChat()->getId() == env('YPP_SOURCE_ID') &&
            $message->getReplyToMessage();
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $text = $message->getReplyToMessage()->getText() ?? $message->getReplyToMessage()->getCaption() ?? '';

        if ($message->getReplyToMessage()->getFrom()->getId() == $telegram->getBotId() && $text != '') {
            if (preg_match('/投稿ID：([A-Z0-9]{16})/', $text, $cvidmatches)) {
                $cvid = $cvidmatches[1];
            }
            if (preg_match('/点击复制ID：(\d*)/', $text, $useridmatches)) {
                $userId = $useridmatches[1];
            }
        }
        if (isset($cvid) && isset($userId)) {
            $userData = Conversation::get($userId, 'contribute');
            $cvInfo = $userData[$cvid] ?? [];
            if (!empty($cvInfo)) {
                $cvname = $cvInfo['name'];
                $data = [
                    'chat_id' => $userId,
                    'text' => ''
                ];
                $data['text'] .= "来自管理员 <a href='tg://user?id={$message->getFrom()->getId()}'>{$message->getFrom()->getFirstName()}</a>(有关您的投稿 <code>{$cvname}</code>): \n";
                $data['text'] .= "\n";
                $data['text'] .= $message->getText();
                $data['text'] .= "\n";
                $data['text'] .= "\n";
                $data['text'] .= "(当前无法对该消息进行回复，如有疑问请联系客服。)\n";
                $this->dispatch(new SendMessageJob($data, null, 0));
                $sender = [
                    'chat_id' => $message->getChat()->getId(),
                    'reply_to_message_id' => $message->getMessageId(),
                    'text' => '已请求回复',
                ];
            }
        }
        if (!isset($sender)) {
            $sender = [
                'chat_id' => $message->getChat()->getId(),
                'reply_to_message_id' => $message->getMessageId(),
                'text' => '未找到对应投稿，无法回复。',
            ];
        }
        $this->dispatch(new SendMessageJob($sender, null, 0));
    }
}
