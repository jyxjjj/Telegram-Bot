<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Jobs\DeleteMessageJob;
use App\Jobs\SendMessageJob;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class ReplyToRejectKeyword extends ContributeStep
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
        if ($message->getReplyToMessage()->getFrom()->getId() == $telegram->getBotId()) {
            if ($message->getReplyToMessage()->getText() && preg_match('/投稿ID:(.{16})/', $message->getReplyToMessage()->getText(), $matches)) {
                $cvid = $matches[1];
                $pendingReply = Conversation::get('reply', 'reject');
                if (isset($pendingReply[$cvid])) {
                    $from_id = $pendingReply[$cvid]['from_id'];
                    $from_nickname = $pendingReply[$cvid]['from_nickname'];
                    $user_id = $pendingReply[$cvid]['user_id'];
                    $message_name = $pendingReply[$cvid]['message_name'];
                    unset($pendingReply[$cvid]);
                    Conversation::save('reply', 'reject', $pendingReply);
                    $data = [
                        'chat_id' => $user_id,
                        'text' => '',
                    ];
                    $data['text'] .= "管理员 <a href='tg://user?id={$from_id}'>{$from_nickname}</a> 已经回复了\n";
                    $data['text'] .= "有关您的投稿 <code>{$message_name}</code>\n";
                    $data['text'] .= "被拒绝的原因：\n";
                    $data['text'] .= $message->getText();
                    $this->dispatch(new SendMessageJob($data, null, 0));
                    $sender = [
                        'chat_id' => $message->getChat()->getId(),
                        'reply_to_message_id' => $message->getMessageId(),
                        'text' => '已请求回复。',
                    ];
                    $this->dispatch(new SendMessageJob($sender, null, 0));
                    $deleter = [
                        'chat_id' => $message->getChat()->getId(),
                        'message_id' => $message->getReplyToMessage()->getMessageId(),
                    ];
                    $this->dispatch(new DeleteMessageJob($deleter, 0));
                }
            }
        }
    }
}