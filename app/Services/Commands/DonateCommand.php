<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class DonateCommand extends BaseCommand
{
    public string $name = 'donate';
    public string $description = 'Donate Infomation';
    public string $usage = '/donate';

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
        $data['text'] .= "\n✥  ✥  ✥  <b>Donate Infomation</b>  ✥  ✥  ✥";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  Thank you for donate this project";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  <b>Telegram Wallet USDT TRC20:</b>";
        $data['text'] .= "\n✥  <code>TLie3XqtwQroiAxmCHT4bWocaUEmAeqEjE</code>";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  <b>Telegram Wallet TON:</b>";
        $data['text'] .= "\n✥  <code>UQBJvcj2LF5-LJuBdYXRG98vTpmPRenf-XqfWx6aaYQxanB1</code>";
        $data['text'] .= "\n✥";
        $data['text'] .= "\n✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥  ✥";
        $digitalOcean = new InlineKeyboardButton([
            'text' => 'Digital Ocean Server',
            'url' => 'https://m.do.co/c/23e8653b361a',
        ]);
        $qqWallet = new InlineKeyboardButton([
            'text' => 'QQ Wallet',
            'url' => 'https://www.desmg.com/api/qr?data=https%3a%2f%2fi.qianbao.qq.com%2fwallet%2fsqrcode.htm%3fm%3dtenpay%26a%3d1%26u%3d773933146%26ac%3d9F6FDA71D576A2D06BDC989DF3BED696727BA4758E68901461F656DA769F2203%26n%3d%e9%be%99%e7%bc%98%e7%a7%91%e6%8a%80%26f%3dwallet',
        ]);
        $alipay = new InlineKeyboardButton([
            'text' => 'Alipay',
            'url' => 'https://www.desmg.com/api/qr?data=https%3a%2f%2fqr.alipay.com%2fFKX006260GRX9PQEAL5C85',
        ]);
        $weiXinPay = new InlineKeyboardButton([
            'text' => 'WeiXin Pay',
            'url' => 'https://www.desmg.com/api/qr?data=https%3a%2f%2fqr.alipay.com%2fFKX006260GRX9PQEAL5C85',
        ]);
        $data['reply_markup'] = new InlineKeyboard([]);
        $data['reply_markup']->addRow($digitalOcean);
        $data['reply_markup']->addRow($qqWallet, $alipay, $weiXinPay);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
