<?php

namespace App\Services\Commands;

use App\Common\RequestService;
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
        $username = $message->getChat()->getUsername();
        $payload = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= env('LONG_START_AD');
        if (str_starts_with($payload, 'get')) {
            $rest = $this->rateLimit($chatId, $username);
            if ($rest == -1) {
                return;
            }
            $data['text'] .= "\n👇👇👇您所获取的链接👇👇👇";
            $cvid = substr($payload, 3);
            $linkData = Conversation::get('link', 'link');
            $link = $linkData[$cvid] ?? "获取链接失败\n请联系管理员";
            $data['text'] .= "\n$link\n";
            $data['text'] .= "您今日剩余获取链接次数：$rest\n";
        }
        $data['text'] .= "\nDMCA及版权反馈、技术支持\n";
        $data['text'] .= "请向本机器人发送 /help 命令\n";
        $data['text'] .= "发送 /donate 获取💰捐💰赠💰信息\n";
        //#region reply_markup
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $data['reply_markup']->addRow(new KeyboardButton('一步投稿'), new KeyboardButton('分步投稿'));
        $data['reply_markup']->addRow(new KeyboardButton('DMCA Request'), new KeyboardButton('版权反馈'));
        $data['reply_markup']->addRow(new KeyboardButton('客服帮助'), new KeyboardButton('技术支持'));
        $data['reply_markup']->addRow(new KeyboardButton('意见建议'), new KeyboardButton('捐赠信息'));
        //#endregion reply_markup
        RequestService::getInstance()->sendMessage($data);
    }
}
