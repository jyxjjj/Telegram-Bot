<?php

namespace App\Jobs;

use App\Common\BotCommon;
use App\Common\Conversation;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class PassPendingJob extends BaseQueue
{
    private string $data;

    public function __construct(string $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        $telegram = BotCommon::getTelegram();
        $bot_name = $telegram->getBotUsername();
        $cvid = $this->data;
        $sender = [
            'chat_id' => env('YPP_TARGET_ID'),
            'text' => '',
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ];
        $sender3 = $sender;
        $sender3['chat_id'] = env('YPP_TARGET_ID_3');
        $pendingData = Conversation::get('pending', 'pending');
        $user_id = $pendingData[$cvid];
        unset($pendingData[$cvid]);
        Conversation::save('pending', 'pending', $pendingData);
        unset($pendingData);
        $userData = Conversation::get($user_id, 'contribute');
        $userData[$cvid]['status'] = 'pass';
        Conversation::save($user_id, 'contribute', $userData);
        Conversation::save($cvid, 'link', ['link' => $userData[$cvid]['link']]);
        $message_pic = $userData[$cvid]['pic'];
        $message_name = $userData[$cvid]['name'];
        $message_desc = $userData[$cvid]['desc'];
        $original_link = $userData[$cvid]['link'];
        $message_link = "<a href='https://t.me/{$bot_name}?start=get{$cvid}'>点击获取</a>";
        $message_tag = $userData[$cvid]['tag'];
        $hasPic = (bool)$message_pic;
        $shortAd = env('SHORT_AD');
        if ($hasPic) {
            unset($sender['text']);
            unset($sender3['text']);
            $sender['photo'] = $message_pic;
            $sender['caption'] = "资源名称：{$message_name}\n\n";
            $sender['caption'] .= "资源简介：{$message_desc}\n\n";
            $sender['caption'] .= "链接：{$message_link}\n\n";
            $sender['caption'] .= "🔍 关键词：{$message_tag}\n\n";
            $sender['caption'] .= "{$shortAd}\n\n";
            $sender3['photo'] = $message_pic;
            $sender3['caption'] = "资源名称：{$message_name}\n\n";
            $sender3['caption'] .= "资源简介：{$message_desc}\n\n";
            $sender3['caption'] .= "链接：{$original_link}\n\n";
            $sender3['caption'] .= "🔍 关键词：{$message_tag}\n\n";
            $sender3['caption'] .= "{$shortAd}\n\n";
            $sender2 = $sender;
            $sender2['chat_id'] = env('YPP_TARGET_ID_2');
            $serverResponse = Request::sendPhoto($sender);
            sleep(1);
            $serverResponse2 = Request::sendPhoto($sender2);
            sleep(1);
            $serverResponse3 = Request::sendPhoto($sender3);
        } else {
            $sender['text'] .= "资源名称：{$message_name}\n\n";
            $sender['text'] .= "资源简介：{$message_desc}\n\n";
            $sender['text'] .= "链接：{$message_link}\n\n";
            $sender['text'] .= "🔍 关键词：{$message_tag}\n\n";
            $sender['text'] .= "{$shortAd}\n\n";
            $sender3['text'] .= "资源名称：{$message_name}\n\n";
            $sender3['text'] .= "资源简介：{$message_desc}\n\n";
            $sender3['text'] .= "链接：{$original_link}\n\n";
            $sender3['text'] .= "🔍 关键词：{$message_tag}\n\n";
            $sender3['text'] .= "{$shortAd}\n\n";
            $sender2 = $sender;
            $sender2['chat_id'] = env('YPP_TARGET_ID_2');
            $serverResponse = Request::sendMessage($sender);
            sleep(1);
            $serverResponse2 = Request::sendMessage($sender2);
            sleep(1);
            $serverResponse3 = Request::sendMessage($sender3);
        }
        if ($serverResponse->isOk()) {
            /** @var Message $sendResult */
            $sendResult = $serverResponse->getResult();
            $messageId = $sendResult->getMessageId();
        } else {
            $errorCode = $serverResponse->getErrorCode();
            $errorDescription = $serverResponse->getDescription();
            Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
            $messageId = 0;
        }
        if ($serverResponse2->isOk()) {
            /** @var Message $sendResult */
            $sendResult2 = $serverResponse2->getResult();
            $messageId2 = $sendResult2->getMessageId();
        } else {
            $errorCode = $serverResponse2->getErrorCode();
            $errorDescription = $serverResponse2->getDescription();
            Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
            $messageId2 = 0;
        }
        if (!$serverResponse3->isOk()) {
            $errorCode = $serverResponse3->getErrorCode();
            $errorDescription = $serverResponse3->getDescription();
            Log::error("Telegram Returned Error($errorCode): $errorDescription", [__FILE__, __LINE__, $this->data]);
        }
        $sender = [
            'chat_id' => $user_id,
            'text' => '',
        ];
        $sender['text'] .= "您的资源<code>{$message_name}</code>已通过审核，已经发布到频道中。\n\n";
        $sender['text'] .= "请点击下方按钮查看频道内消息，如未看到“查看频道消息”按钮，或按钮无法正常跳转，则说明发送到频道时遇到问题，请联系Bot技术支持。\n\n";
        $sender['reply_markup'] = new InlineKeyboard([]);
        if ($messageId != 0) {
            $chatIdForLink = substr(env('YPP_TARGET_ID'), 4);
            $button = new InlineKeyboardButton([
                'text' => '查看主频道消息',
                'url' => "https://t.me/c/{$chatIdForLink}/{$messageId}",
            ]);
            $sender['reply_markup']->addRow($button);
        }
        if ($messageId2 != 0) {
            $chatIdForLink2 = substr(env('YPP_TARGET_ID_2'), 4);
            $buttonb = new InlineKeyboardButton([
                'text' => '查看备份频道消息',
                'url' => "https://t.me/c/{$chatIdForLink2}/{$messageId2}",
            ]);
            $sender['reply_markup']->addRow($buttonb);
        }
        $button1 = new InlineKeyboardButton([
            'text' => '技术支持',
            'url' => "https://t.me/jyxjjj",
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => '联系客服',
            'url' => "https://t.me/zaihua_bot",
        ]);
        $sender['reply_markup'] = $sender['reply_markup']->addRow($button1, $button2);
        sleep(1);
        SendMessageJob::dispatch($sender, null, 0);
    }
}
