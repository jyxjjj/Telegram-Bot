<?php

namespace App\Services\Keywords;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class CancelContributeKeyword extends ContributeStep
{
    public function preExecute(Message $message): bool
    {
        return $message->getChat()->isPrivateChat() && $message->getText() === '取消投稿';
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $sender = [
            'chat_id' => $message->getChat()->getId(),
            'text' => '',
        ];
        $data = Conversation::get($message->getChat()->getId(), 'contribute');
        if (count($data) > 0 && ($data['status'] != 'contribute' || $data['status'] != 'contribute2')) {
            $sender['text'] .= "请先开始投稿。\n";
        } else {
            $data['status'] = 'free';
            $cvid = $data['cvid'];
            unset($data['cvid'], $data[$cvid]);
            Conversation::save($message->getChat()->getId(), 'contribute', $data);
            $sender['text'] .= "投稿已取消。\n";
        }
        $sender['reply_markup'] = new Keyboard([]);
        $sender['reply_markup']->setResizeKeyboard(true);
        $sender['reply_markup']->addRow(new KeyboardButton('阿里云盘投稿'));
        $sender['reply_markup']->addRow(new KeyboardButton('阿里云盘一步投稿'));
        $this->dispatch((new SendMessageJob($sender, null, 0)));
    }
}
