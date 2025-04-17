<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Schedule;

use App\Common\RequestHelper;
use App\Jobs\SendMessageJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

class TRC20Monitor extends Command
{
    protected $signature = 'trc20transpusher';
    protected $description = 'Push Transactions';

    public function handle(): int
    {
        $list = [
            'THue4NmwEVF8HKV6y9aHzanmWMQSsMMMMM' => ['@LZSMIAO ', '-1002117558170'],
            'TQFRgPM9RuCpCffZGwnfs1ENtnrJt9oRJn' => ['@linzeen ', '-1001743989979'],
        ];
        foreach ($list as $address => [$user, $chat]) {
            $text = '';
            $cacheKey = 'TRC20Monitor_' . $address;
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
                    'chat_id' => $chat,
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
        try {
            $data = RequestHelper::getInstance()
                ->withHeader('TRON-PRO-API-KEY', env('TRON_PRO_API_KEY'))
                ->get($url);
        } catch (Throwable) {
            return [];
        }
        $data = $data->json();
        if (!isset($data['token_transfers'])) {
            return [];
        }
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
