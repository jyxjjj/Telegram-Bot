<?php

namespace App\Services\Commands;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class GetTransHashesCommand extends BaseCommand
{
    public string $name = 'gettranshashes';
    public string $description = 'Get transaction hashes';
    public string $usage = '/gettranshashes';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $params = $message->getText(true);
        $address = trim($params);
        if (empty($address)) {
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'reply_to_message_id' => $message->getMessageId(),
                'text' => '请输入正确的USDT TRC20地址',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => $this->get3Transactions($address),
        ];
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    private function get3Transactions(string $address): string
    {
        $url = 'https://apilist.tronscan.org/api/new/token_trc20/transfers?limit=3&toAddress=' . $address;
        $data = Http::withHeaders(Config::CURL_HEADERS)
            ->withHeader('TRON-PRO-API-KEY', env('TRON_PRO_API_KEY'))
            ->get($url);
        $data = $data->json();
        $data = $data['token_transfers'];
        $text = '暂无交易：这个地址上没有任何交易，它就这样漫无目的的漂浮在区块链上。';
        foreach ($data as $item) {
            if ($item['confirmed'] != 'true') {
                continue;
            }
            $text == '暂无交易：这个地址上没有任何交易，它就这样漫无目的的漂浮在区块链上。' && $text = '';
            $text .= '交易哈希: ' . $item['transaction_id'] . PHP_EOL;
            $text .= '金额: ' . number_format(bcdiv($item['quant'], 1000000, 16), 6, '.', '') . PHP_EOL;
        }
        return $text;
    }
}
