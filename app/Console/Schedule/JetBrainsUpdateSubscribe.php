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

use App\Common\ERR;
use App\Common\RequestHelper;
use App\Jobs\SendMessageJob;
use App\Models\TUpdateSubscribes;
use DESMG\RFC6986\Hash;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

class JetBrainsUpdateSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:jetbrains';
    protected $description = 'Get JetBrains Products Newest Version then push to target chat';
    private array $PRODUCT_CODES = [
        'CL' => 'CLion',
        'DG' => 'DataGrip',
        'GO' => 'GoLand',
        'IIC' => 'IntelliJ IDEA Community',
        'IIU' => 'IntelliJ IDEA Ultimate',
        'PCC' => 'PyCharm Community',
        'PCP' => 'PyCharm Professional',
        'PS' => 'PhpStorm',
        'RD' => 'Rider',
        'RM' => 'RubyMine',
        'WS' => 'WebStorm',
    ];
    private array $versions = [];
    private array $downloads = [];

    public function handle(): int
    {
        try {
            $this->getUpdate();
            $datas = TUpdateSubscribes::getAllSubscribeBySoftware('JetBrains');
            foreach ($datas as $data) {
                $chat_id = $data['chat_id'];
                $string = $this->getUpdateData($chat_id);
                $hash = Hash::sha512(str_replace(' (NEW)', '', $string));
                $message = [
                    'chat_id' => $chat_id,
                    'text' => $string,
                ];
                $lastSend = $this->getLastSend($chat_id);
                if (!$lastSend) {
                    $this->dispatch(new SendMessageJob($message, null, 0));
                    $this->setLastSend($chat_id, $hash);
                } else {
                    if ($lastSend != $hash) {
                        $this->dispatch(new SendMessageJob($message, null, 0));
                        $this->setLastSend($chat_id, $hash);
                    }
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            ERR::log($e);
            return self::FAILURE;
        }
    }

    /**
     * @return void
     * @throws ConnectionException
     */
    private function getUpdate(): void
    {
        $data = $this->getJson();
        $codes = array_keys($this->PRODUCT_CODES);
        foreach ($codes as $code) {
            if (isset($data[$code][0]['build']) && isset($data[$code][0]['downloads']['macM1']['link'])) {
                $this->versions[$code] = $data[$code][0]['build'];
                $this->downloads[$code] = $data[$code][0]['downloads']['macM1']['link'];
            }
        }
    }

    /**
     * @return array
     * @throws ConnectionException
     */
    private function getJson(): array
    {
        return RequestHelper::getInstance()
            ->get($this->getURL())
            ->json();
    }

    /**
     * @return string
     */
    private function getURL(): string
    {
        $base = 'https://data.services.jetbrains.com/products/releases?latest=true&type=release&code=';
        $codes = array_keys($this->PRODUCT_CODES);
        $param = implode(',', $codes);
        return $base . $param;
    }

    /**
     * @param int $chat_id
     * @return string
     */
    private function getUpdateData(int $chat_id): string
    {
        $data = '<b>New Versions of JetBrains Products Updated</b>' . PHP_EOL . '================' . PHP_EOL;
        $codes = array_keys($this->PRODUCT_CODES);
        foreach ($codes as $code) {
            if (isset($this->versions[$code]) && isset($this->downloads[$code])) {
                $lastVersion = $this->getLastVersion($chat_id, $code);
                if (!$lastVersion) {
                    $this->setLastVersion($chat_id, $code, $this->versions[$code]);
                } else {
                    if ($lastVersion != $this->versions[$code]) {
                        $this->setLastVersion($chat_id, $code, $this->versions[$code]);
                        $this->versions[$code] .= ' (NEW)';
                    }
                }
                $data .= "<b>{$this->PRODUCT_CODES[$code]}</b>: <code>{$this->versions[$code]}</code>\n";
                $data .= "    >>> <a href=\"{$this->downloads[$code]}\">Download {$this->PRODUCT_CODES[$code]} for macOS ARM64</a>\n";
            }
        }
        return $data;
    }

    /**
     * @param int $chat_id
     * @param string $key
     * @return string|false
     */
    private function getLastVersion(int $chat_id, string $key): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_version::$chat_id::JetBrains::$key", false);
    }

    /**
     * @param int $chat_id
     * @param string $key
     * @param        $version
     * @return bool
     */
    private function setLastVersion(int $chat_id, string $key, $version): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_version::$chat_id::JetBrains::$key", $version, Carbon::now()->addMonths(3));
    }

    /**
     * @param int $chat_id
     * @return string|false
     */
    private function getLastSend(int $chat_id): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_send::$chat_id::JetBrains", false);
    }

    /**
     * @param int $chat_id
     * @param string $hash
     * @return bool
     */
    private function setLastSend(int $chat_id, string $hash): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_send::$chat_id::JetBrains", $hash, Carbon::now()->addMonths(3));
    }
}
