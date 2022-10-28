<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Carbon\Carbon;
use Longman\TelegramBot\Entities\ChatPermissions;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class RestrictMemberJob extends TelegramBaseQueue
{
    private array $data;
    private int $time;

    /**
     * @param array $data
     * @param int $time
     */
    public function __construct(array $data, int $time)
    {
        parent::__construct();
        $this->data = $data;
        $this->time = $time;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        BotCommon::getTelegram();
        $origin = $this->data;
        $restricter = [
            'chat_id' => $origin['chatId'],
            'user_id' => $origin['restrictUserId'],
            'until_date' => Carbon::now()->addSeconds($this->time)->getTimestamp(),
            'permissions' => new ChatPermissions([
                'can_send_messages' => false,
                'can_send_media_messages' => false,
                'can_send_polls' => false,
                'can_send_other_messages' => false,
                'can_add_web_page_previews' => false,
                'can_change_info' => false,
                'can_invite_users' => false,
                'can_pin_messages' => false,
            ]),
        ];
        $sender = [
            'chat_id' => $origin['chatId'],
            'reply_to_message_id' => $origin['messageId'],
            'text' => '',
        ];
        $serverResponse = Request::restrictChatMember($restricter);
        if ($serverResponse->isOk()) {
            $sender['text'] .= "*User restricted for {$this->time} seconds.*\n";
            $sender['text'] .= "*Until:* {$restricter['until_date']}\n";
            $sender['text'] .= "*User ID:* [{$origin['restrictUserId']}](tg://user?id={$origin['restrictUserId']})\n";
        } else {
            $sender['text'] .= "*Error restricting user.*\n";
            $sender['text'] .= "*Error Code:* `{$serverResponse->getErrorCode()}`\n";
            $sender['text'] .= "*Error Msg:* `{$serverResponse->getDescription()}`\n";
        }
        SendMessageJob::dispatch($sender);
    }
}
