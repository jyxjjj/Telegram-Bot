<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Common\Log\BL;
use App\Jobs\SendMessageJob;
use DESMG\RFC4122\UUID;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AliYunPanOnceKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return $message->getChat()->isPrivateChat() && $message->getText() === '阿里云盘一步投稿';
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $blackList = BL::get($message->getChat()->getId());
        if ($blackList) {
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text' => "您在黑名单中，无法投稿，请联系客服。\n",
            ];
            $data['reply_markup'] = (new InlineKeyboard([]))->addRow(new InlineKeyboardButton(['text' => '联系客服', 'url' => 'https://t.me/zaihuabot'],));
            $this->dispatch((new SendMessageJob($data, null, 0))->delay(0));
            return;
        }
        $data = Conversation::get($message->getChat()->getId(), 'contribute');
        if (count($data) > 0 && $data['status'] != 'free') {
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text' => "您已经在投稿流程中了，无法再次投稿。\n",
            ];
            $data['reply_markup'] = new Keyboard([]);
            $data['reply_markup']->setResizeKeyboard(true);
            $data['reply_markup']->addRow(new KeyboardButton('取消投稿'));
            $this->dispatch((new SendMessageJob($data, null, 0))->delay(0));
            return;
        }
        $cvid = UUID::generateTinyUniqueID();
        $data['status'] = 'contribute2';
        $data['cvid'] = $cvid;
        $data[$cvid]['status'] = 'once';
        Conversation::save($message->getChat()->getId(), 'contribute', $data);
        $data = [
            'chat_id' => $message->getChat()->getId(),
            'text' => "🥳 欢迎投稿 ~\n\n一步投稿，必须包含图片。自组格式，发送给机器人即可。如若多次提示投稿错误，请检查投稿格式和下方是否一致。联系 @zaihua_bot\n\n请阅读<a href='https://t.me/yunpanpan/14438'>投稿格式要求</a>，继续操作即代表您已知晓其内容。\n",
        ];
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $data['reply_markup']->addRow(new KeyboardButton('取消投稿'));
        $this->dispatch((new SendMessageJob($data, null, 0))->delay(0));
    }
}
