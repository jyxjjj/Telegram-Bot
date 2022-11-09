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
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $payload = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
//        $payload && $data['text'] .= "<b>本次启动参数:</b> <code>$payload</code>\n";
        $data['text'] .= "你好，欢迎使用在花投稿机器人2.0。\n";
        $data['text'] .= "命令列表请输入 /help 。\n";
        $data['text'] .= "<b>你的用户ID：</b> <a href='tg://user?id={$userId}'>{$userId}</a>\n";
        $data['text'] .= "使用问题及建议联系： @zaihua_bot \n";
        $data['text'] .= "技术支持请联系： @jyxjjj \n";
        $data['text'] .= "我们提供了DMCA及其他版权问题反馈通道\n";
        $data['text'] .= "如您有任何版权相关问题，请联系： @zaihua_bot\n";
        $data['text'] .= env('AD_TEXT');
        if (str_starts_with($payload, 'get')) {
            $cvid = substr($payload, 3);
            $linkData = Conversation::get($cvid, 'link');
            $link = $linkData['link'] ?? '';
            $data['text'] .= "\n\n链接地址： {$link}\n\n";
        }
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $button1 = new KeyboardButton('阿里云盘投稿');
        $data['reply_markup']->addRow($button1);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
