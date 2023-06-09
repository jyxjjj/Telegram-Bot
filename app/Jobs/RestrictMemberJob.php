<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Carbon;
use Longman\TelegramBot\Entities\ChatPermissions;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class RestrictMemberJob extends BaseQueue
{
    private array $data;
    private int $time;
    private bool $revoke;

    /**
     * @param array $data
     * @param int $time
     * @param bool $revoke
     */
    public function __construct(array $data, int $time, bool $revoke = false)
    {
        parent::__construct();
        $this->data = $data;
        $this->time = $time;
        $this->revoke = $revoke;
    }

    /**
     * @throws TelegramException
     */
    public function handle(): void
    {
        BotCommon::getTelegram();
        $origin = $this->data;
        $restricter = [
            'chat_id' => $origin['chat_id'],
            'user_id' => $origin['user_id'],
            'until_date' => Carbon::now()->addSeconds($this->time)->getTimestamp(),
            'permissions' => new ChatPermissions(
                $this->revoke ? [
                    'can_send_messages' => true,
                    'can_send_media_messages' => true,
                    'can_send_polls' => true,
                    'can_send_other_messages' => true,
                    'can_add_web_page_previews' => true,
                    'can_change_info' => false,
                    'can_invite_users' => false,
                    'can_pin_messages' => false,
                    'can_manage_topics' => false,
                ] : [
                    'can_send_messages' => false,
                    'can_send_media_messages' => false,
                    'can_send_polls' => false,
                    'can_send_other_messages' => false,
                    'can_add_web_page_previews' => false,
                    'can_change_info' => false,
                    'can_invite_users' => false,
                    'can_pin_messages' => false,
                    'can_manage_topics' => false,
                ]
            ),
        ];
        $sender = [
            'chat_id' => $origin['chat_id'],
            'reply_to_message_id' => $origin['message_id'],
            'text' => '',
        ];
        $serverResponse = Request::restrictChatMember($restricter);
        if ($serverResponse->isOk()) {
            $this->revoke && $sender['text'] .= "<b>User unrestricted.</b>\n";
            $this->revoke && $sender['text'] .= "<b>User ID</b>: <a href='tg://user?id={$origin['user_id']}'>{$origin['user_id']}</a>\n";
            !$this->revoke && $sender['text'] .= "<b>User restricted for $this->time seconds.</b>\n";
            !$this->revoke && $sender['text'] .= "<b>Until</b>: {$restricter['until_date']}\n";
            !$this->revoke && $sender['text'] .= "<b>User ID</b>: <a href='tg://user?id={$origin['user_id']}'>{$origin['user_id']}</a>\n";
        } else {
            $sender['text'] .= "<b>Error restricting user.</b>\n";
            $sender['text'] .= "<b>Error Code</b>: <code>{$serverResponse->getErrorCode()}</code>\n";
            $sender['text'] .= "<b>Error Msg</b>: <code>{$serverResponse->getDescription()}</code>\n";
        }
        SendMessageJob::dispatch($sender);
    }
}
