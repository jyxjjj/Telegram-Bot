<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Common\Conversation;
use App\Jobs\Base\BaseQueue;
use Exception;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Exception\TelegramException;

class RejectPendingJob extends BaseQueue
{
    private string|array $data;
    private bool $needsReply;

    /**
     * @throws Exception
     */
    public function __construct(string|array $data, bool $needsReply = false)
    {
        parent::__construct();
        if ($needsReply && is_array($data)) {
            $this->data = $data;
            $this->needsReply = $needsReply;
        } else if (!$needsReply && is_string($data)) {
            $this->data = $data;
            $this->needsReply = $needsReply;
        } else {
            throw new Exception('Invalid data type');
        }
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        BotCommon::getTelegram();
        $data = $this->data;
        $needsReply = $this->needsReply;
        if ($needsReply) {
            $chatId = $data['chat_id'];
            $messageId = $data['message_id'];
            $fromId = $data['from_id'];
            $fromNickname = $data['from_nickname'];
            $cvid = $data['cvid'];
        } else {
            $cvid = $data;
        }
        $pendingData = Conversation::get('pending', 'pending');
        if (!isset($pendingData[$cvid])) {
            return;
        }
        $user_id = $pendingData[$cvid];
        unset($pendingData[$cvid]);
        Conversation::save('pending', 'pending', $pendingData);
        unset($pendingData);
        $userData = Conversation::get($user_id, 'contribute');
        $userData[$cvid]['status'] = 'reject';
        Conversation::save($user_id, 'contribute', $userData);
        $sender = [
            'chat_id' => $user_id,
            'text' => '',
        ];
        $message_name = $userData[$cvid]['name'];
        $sender['text'] .= "您提交的资源<code>{$message_name}</code>已被拒绝。";
        $needsReply && $sender['text'] .= "管理员选择回复您拒绝理由，请稍候。";
        $sender['reply_markup'] = new InlineKeyboard([]);
        $button1 = new InlineKeyboardButton([
            'text' => '技术支持',
            'url' => "https://t.me/jyxjjj",
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => '联系客服',
            'url' => "https://t.me/zaihua_bot",
        ]);
        $sender['reply_markup'] = $sender['reply_markup']->addRow($button1, $button2);
        SendMessageJob::dispatch($sender, null, 0);
        if ($needsReply) {
            $pendingReply = Conversation::get('reply', 'reject');
            $pendingReply[$cvid] = [
                'from_id' => $fromId,
                'from_nickname' => $fromNickname,
                'user_id' => $user_id,
                'message_name' => $message_name,
            ];
            Conversation::save('reply', 'reject', $pendingReply);
            $reply = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => '',
            ];
            $reply['text'] .= "请<a href='tg://user?id={$fromId}'>{$fromNickname}</a>回复本条消息，以告知用户拒绝理由。\n\n";
            $reply['text'] .= "投稿ID:$cvid\n\n";
            SendMessageJob::dispatch($reply, null, 0);
        }
    }
}
