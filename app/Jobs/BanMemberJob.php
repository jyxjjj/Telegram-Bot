<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Carbon\Carbon;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class BanMemberJob extends TelegramBaseQueue
{
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        BotCommon::getTelegram();
        $origin = $this->data;
        $banner = [
            'chat_id' => $origin['chat_id'],
            'user_id' => $origin['user_id'],
            'until_date' => Carbon::now()->addSecond()->getTimestamp(),
            'revoke_messages' => true,
        ];
        $sender = [
            'chat_id' => $origin['chat_id'],
            'reply_to_message_id' => $origin['message_id'],
            'text' => '',
        ];
        $serverResponse = Request::banChatMember($banner);
        if ($serverResponse->isOk()) {
            $sender['text'] .= "*User banned from chat.*\n";
            $sender['text'] .= "*User ID:* [{$origin['user_id']}](tg://user?id={$origin['user_id']})\n";
        } else {
            $sender['text'] .= "*Error banning user.*\n";
            $sender['text'] .= "*Error Code:* `{$serverResponse->getErrorCode()}`\n";
            $sender['text'] .= "*Error Msg:* `{$serverResponse->getDescription()}`\n";
        }
        SendMessageJob::dispatch($sender);
    }
}
