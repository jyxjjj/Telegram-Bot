<?php

namespace App\Services\Keywords;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseKeyword;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class B23UIDToLinkKeyword extends BaseKeyword
{
    public string $name = 'b23 uid to link';
    public string $description = 'generate link from b23 uid';
    protected string $pattern = '/UID:(\d+)/';

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $text = $message->getText(true) ?? $message->getCaption();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $this->handle($text, $data);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function handle(string $text, array &$data)
    {
        if (preg_match_all($this->pattern, $text, $matches)) {
            $data['text'] .= "Bilibili UID Detected\n";
            $data['text'] .= "<b>Warning</b>: UID detected does not necessarily mean Bilibili UID\n\n";
            $data['reply_markup'] = new InlineKeyboard([]);
            if (isset($matches[1]) && isset($matches[1][0])) {
                $data['text'] .= "UID: <code>{$matches[1][0]}</code>\n";
                $data['text'] .= "Link: <code>https://space.bilibili.com/{$matches[1][0]}</code>\n";
                $data['reply_markup']->addRow(
                    new InlineKeyboardButton([
                        'text' => "UID: {$matches[1][0]}",
                        'url' => "https://space.bilibili.com/{$matches[1][0]}",
                    ])
                );
            }
        }
    }
}
