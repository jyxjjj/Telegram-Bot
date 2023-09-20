<?php

namespace App\Console\Schedule;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TRC20Monitor extends Command
{
    protected $signature = 'trc20transpusher';
    protected $description = 'Push Transactions';

    public function handle(): int
    {
        $list = [
        ];
        foreach ($list as $address => $user) {
            $text = '';
            $cacheKey = 'TRC20Monitor' . $address;
            $alreadyPushedHashes = Cache::get($cacheKey);
            if (empty($alreadyPushedHashes) || !is_array($alreadyPushedHashes)) {
                $alreadyPushedHashes = [];
            }
            $newOrders = $this->getTransaction($address);
            foreach ($newOrders as $newOrder) {
                if (!in_array($newOrder['hash'], $alreadyPushedHashes)) {
                    $alreadyPushedHashes[] = $newOrder['hash'];
                    $text .= '时间: ' . $newOrder['ts'] . PHP_EOL;
                    $text .= '交易哈希: ' . $newOrder['hash'] . PHP_EOL;
                    $text .= '金额: ' . $newOrder['amount'] . PHP_EOL . PHP_EOL;
                }
            }
            if ($text != '') {
                Cache::put($cacheKey, $alreadyPushedHashes, Carbon::now()->addDays(90));
                $text = $user . 'TRC20交易提醒' . PHP_EOL . $text;
                $data = [
                    'text' => $text,
                ];
                dispatch(new SendMessageJob($data, null, 0));
            }
        }
        return self::SUCCESS;
    }

    private function getTransaction(string $address): array
    {
        $url = 'https://apilist.tronscan.org/api/new/token_trc20/transfers?limit=3&toAddress=' . $address;
        $data = Http::withHeaders(Config::CURL_HEADERS)
            ->withHeader('TRON-PRO-API-KEY', env('TRON_PRO_API_KEY'))
            ->get($url);
        $data = $data->json();
        $data = $data['token_transfers'];
        $result = [];
        foreach ($data as $item) {
            if ($item['confirmed'] != 'true') {
                continue;
            }
            $hash = $item['transaction_id'];
            $amount = number_format(bcdiv($item['quant'], 1000000, 16), 6, '.', '');
            $ts = Carbon::createFromTimestampMs($item['block_ts'], 'UTC')->setTimezone('Etc/GMT-8')->format('Y-m-d H:i:s');
            $result[] = [
                'hash' => $hash,
                'amount' => $amount,
                'ts' => $ts,
            ];
        }
        return count($result) > 0 ? $result : [];
    }
}
