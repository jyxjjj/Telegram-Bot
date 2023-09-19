<?php

namespace App\Services\Commands;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class DonateCommand extends BaseCommand
{
    public string $name = 'donate';
    public string $description = 'Donate command';
    public string $usage = '/donate';
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
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= Conversation::get('ad', 'ad')[2] ?? '';
        $data['text'] .= "\n👇👇👇捐赠信息👇👇👇";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['text'] .= "\n✥  如果您愿意用资金支持本项目";
        $data['text'] .= "\n✥  捐赠的资金会被用于分担服务器费用、改善服务器配置";
        $data['text'] .= "\n✥  以及激励我继续更新机器人";
        $data['text'] .= "\n✥  不论金额大小，都感谢您的支持";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['text'] .= "\n✥  <a href='https://m.do.co/c/23e8653b361a'>推荐购买服务器</a>";
        $data['text'] .= "\n✥  <b>Telegram Wallet USDT TRC20:</b>";
        $data['text'] .= "\n✥  <code>TLie3XqtwQroiAxmCHT4bWocaUEmAeqEjE</code>";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['text'] .= "\n👆👆👆捐赠信息👆👆👆";
        $data['text'] .= "\n\nDMCA及版权反馈、技术支持\n";
        $data['text'] .= "请向本机器人发送 /help 命令\n";
        $data['reply_markup'] = new InlineKeyboard([]);
        $button = new InlineKeyboardButton([
            'text' => '开源方捐赠信息',
            'url' => 'https://www.desmg.com/donate',
        ]);
        $data['reply_markup']->addRow($button);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
