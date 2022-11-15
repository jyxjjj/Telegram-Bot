<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use DESMG\RFC4122\UUID;
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
            'text' => "🥳 欢迎投稿 ~\n分步投稿支持不包含或一张图片，多条链接，一步投稿必须包含图片，仅支持一张图片，一条链接。\n请阅读<a href='https://t.me/yunpanpan/14438'>投稿格式要求</a>，继续操作即代表您已知晓其内容。\n",
        ];
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $data['reply_markup']->addRow(new KeyboardButton('取消投稿'));
        $this->dispatch((new SendMessageJob($data, null, 0))->delay(0));
    }
}
