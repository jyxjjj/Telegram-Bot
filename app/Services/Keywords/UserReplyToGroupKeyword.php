<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class UserReplyToGroupKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return $message->getChat()->isPrivateChat() && $message->getReplyToMessage();
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $text = $message->getReplyToMessage()->getText() ?? $message->getReplyToMessage()->getCaption() ?? '';
        $userId = $message->getChat()->getId();
        if ($message->getReplyToMessage()->getFrom()->getId() == $telegram->getBotId() && $text != '') {
            if (preg_match('/\[投稿ID]:([A-Z0-9]{16})/', $text, $cvidmatches)) {
                $cvid = $cvidmatches[1];
            }
        } else {
            return;
        }
        if (isset($cvid) && isset($userId)) {
            $userData = Conversation::get($userId, 'contribute');
            $cvInfo = $userData[$cvid] ?? [];
            if (!empty($cvInfo)) {
                $cvname = $cvInfo['name'];
                $data = [
                    'chat_id' => env('YPP_SOURCE_ID'),
                    'text' => ''
                ];
                $data['text'] .= "来自用户有关投稿 <code>$cvname</code> : \n";
                $data['text'] .= "\n";
                $data['text'] .= $message->getText();
                $data['text'] .= "\n";
                $data['text'] .= "\n";
                $data['text'] .= "投稿ID：$cvid\n";
                $data['text'] .= "点击复制ID：$userId\n";
                $this->dispatch(new SendMessageJob($data, null, 0));
                $sender = [
                    'chat_id' => $message->getChat()->getId(),
                    'reply_to_message_id' => $message->getMessageId(),
                    'text' => '已请求回复',
                ];
                $this->dispatch(new SendMessageJob($sender, null, 0));
            }
        }
    }
}
