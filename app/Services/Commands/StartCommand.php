<?php

namespace App\Services\Commands;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class StartCommand extends BaseCommand
{
    public string $name = 'start';
    public string $description = 'Start command';
    public string $usage = '/start';
    public bool $private = true;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        if (!$message->getChat()->isPrivateChat()) {
            return;
        }
        $chatId = $message->getChat()->getId();
        $payload = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
//        $payload && $data['text'] .= "<b>本次启动参数</b>: <code>$payload</code>\n";
        $data['text'] .= env('LONG_START_AD');
        if (str_starts_with($payload, 'get')) {
            $cvid = substr($payload, 3);
            $linkData = Conversation::get($cvid, 'link');
            $link = $linkData['link'] ?? "获取链接失败(错误1)\n请联系管理员";
            if ($link == "获取链接失败(错误1)\n请联系管理员") {
                $linkData = Conversation::get('link', 'link');
                $link = $linkData[$cvid] ?? "获取链接失败(错误2)\n请联系管理员";
            }
            $data['text'] .= "\n👇👇👇您所获取的链接👇👇👇";
            $data['text'] .= "\n$link\n";
        }
        $data['text'] .= "\nDMCA及版权反馈、技术支持\n";
        $data['text'] .= "请向本机器人发送 /help 命令\n";
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $button1 = new KeyboardButton('阿里云盘分步投稿');
        $button2 = new KeyboardButton('阿里云盘一步投稿');
        $data['reply_markup']->addRow($button1);
        $data['reply_markup']->addRow($button2);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
