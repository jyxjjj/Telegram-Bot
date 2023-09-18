<?php

namespace App\Services\Commands;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
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
        $data['text'] .= "\n✥  <a href='https://m.do.co/c/23e8653b361a'>推荐购买服务器</a>";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['text'] .= "\n✥  <a href='https://www.desmg.com/api/qr?data=https%3a%2f%2fi.qianbao.qq.com%2fwallet%2fsqrcode.htm%3fm%3dtenpay%26a%3d1%26u%3d773933146%26ac%3d9F6FDA71D576A2D06BDC989DF3BED696727BA4758E68901461F656DA769F2203%26n%3d%e9%be%99%e7%bc%98%e7%a7%91%e6%8a%80%26f%3dwallet'>QQ钱包</a>";
        $data['text'] .= "\n✥  <a href='https://www.desmg.com/api/qr?data=https%3a%2f%2fqr.alipay.com%2fFKX006260GRX9PQEAL5C85'>支付宝</a>";
        $data['text'] .= "\n✥  <a href='https://www.desmg.com/api/qr?data=wxp%3a%2f%2ff2f0PL8c5TC6WxzfirXw5ESmJkE8Mi4I3oaN'>微信支付</a>";
        $data['text'] .= "\n✥  <b>Telegram Wallet USDT TRC20:</b>";
        $data['text'] .= "\n✥  <code>TLie3XqtwQroiAxmCHT4bWocaUEmAeqEjE</code>";
        $data['text'] .= "\n✥  <b>Telegram Wallet TON:</b>";
        $data['text'] .= "\n✥  <code>UQBJvcj2LF5-LJuBdYXRG98vTpmPRenf-XqfWx6aaYQxanB1</code>";
        $data['text'] .= "\n✥  <b>Tonkeeper Wallet TON:</b>";
        $data['text'] .= "\n✥  <code>EQDBXCGojIJphzxX2LpqI24hQdcIswoIKpknCSUG4S7atn5B</code>";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $data['text'] .= "\n👆👆👆捐赠信息👆👆👆";
        $data['text'] .= "\n\nDMCA及版权反馈、技术支持\n";
        $data['text'] .= "请向本机器人发送 /help 命令\n";
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
