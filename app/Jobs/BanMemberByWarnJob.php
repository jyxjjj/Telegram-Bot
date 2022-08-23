<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Carbon\Carbon;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class BanMemberByWarnJob extends TelegramBaseQueue
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
            'chat_id' => $origin['chatId'],
            'user_id' => $origin['banUserId'],
            'until_date' => Carbon::now()->addSecond()->getTimestamp(),
            'revoke_messages' => true,
        ];
        $serverResponse = Request::banChatMember($banner);

        $sender = [
            'chat_id' => $origin['chatId'],
            'reply_to_message_id' => $origin['messageId'],
            'text' => '',
        ];
        if ($serverResponse->isOk()) {
            $sender['text'] .= "*User banned from chat.*\n";
            $sender['text'] .= "*User ID:* [{$origin['banUserId']}](tg://user?id={$origin['banUserId']})\n";
        } else {
            $sender['text'] .= "*Error banning user.*\n";
            $sender['text'] .= "*Error Code:* `{$serverResponse->getErrorCode()}`\n";
            $sender['text'] .= "*Error Msg:* `{$serverResponse->getDescription()}`\n";
        }
        SendMessageJob::dispatch($sender);
    }
}
