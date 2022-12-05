<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\TelegramBaseQueue;
use Illuminate\Support\Carbon;
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
            $sender['text'] .= "<b>User banned from chat.</b>\n";
            $sender['text'] .= "<b>User ID</b>: <a href='tg://user?id={$origin['user_id']}'>{$origin['user_id']}</a>\n";
        } else {
            $sender['text'] .= "<b>Error banning user.</b>\n";
            $sender['text'] .= "<b>Error Code</b>: <code>{$serverResponse->getErrorCode()}</code>\n";
            $sender['text'] .= "<b>Error Msg</b>: <code>{$serverResponse->getDescription()}</code>\n";
        }
        SendMessageJob::dispatch($sender);
    }
}
