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

    /**
     * @throws Exception
     */
    public function __construct(string|array $data)
    {
        parent::__construct();
        if (is_string($data)) {
            $this->data = $data;
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
        $cvid = $data;
        $pendingData = Conversation::get('pending', 'pending');
        if (!isset($pendingData[$cvid])) {
            return;
        }
        $user_id = $pendingData[$cvid];
        unset($pendingData[$cvid]);
        Conversation::save('pending', 'pending', $pendingData);
        unset($pendingData);
        $userData = Conversation::get($user_id, 'contribute');
        unset($userData[$cvid]);
        Conversation::save($user_id, 'contribute', $userData);
        $sender = [
            'chat_id' => $user_id,
            'text' => '',
        ];
        $message_name = $userData[$cvid]['name'];
        $sender['text'] .= "您提交的资源<code>$message_name</code>已被拒绝。";
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
    }
}
