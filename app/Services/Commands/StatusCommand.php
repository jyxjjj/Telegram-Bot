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

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class StatusCommand extends BaseCommand
{
    public string $name = 'status';
    public string $description = 'Show the status of the bot';
    public string $usage = '/status';

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
        $stat1 = file_get_contents('/proc/stat');
        sleep(1);
        $stat2 = file_get_contents('/proc/stat');
        $cpuNums = $this->getCpuNums();
        $times1 = $this->getCpuStat($stat1);
        $times2 = $this->getCpuStat($stat2);
        if (count($times1) == 10 && count($times2) == 10) {
            $cpuUsage = $this->getCpuUsage($times1, $times2);
        } else {
            $cpuUsage = 'Unknown';
        }
        $data['text'] .= "<b>Num of Cores</b>: <code>$cpuNums</code>\n";
        $data['text'] .= "<b>CPU Usage</b>: <code>$cpuUsage%</code>\n";
        $memInfo = file_get_contents('/proc/meminfo');
        $memInfo = explode("\n", $memInfo);
        $memTotal = $this->getMemTotal($memInfo);
        $memFree = $this->getMemFree($memInfo);
        $memAvailable = $this->getMemAvailable($memInfo);
        $memUsed = $memTotal - $memFree;
        $memUsage = number_format($memUsed / $memTotal * 100, 2, '.', '');
        $memTotal = number_format($memTotal / 1024 / 1024, 2, '.', '');
        $memFree = number_format($memFree / 1024 / 1024, 2, '.', '');
        $memAvailable = number_format($memAvailable / 1024 / 1024, 2, '.', '');
        $memUsed = number_format($memUsed / 1024 / 1024, 2, '.', '');
        $data['text'] .= "<b>Total Memory</b>: <code>$memTotal GiB</code>\n";
        $data['text'] .= "<b>Free Memory</b>: <code>$memFree GiB</code>\n";
        $data['text'] .= "<b>Available Memory</b>: <code>$memAvailable GiB</code>\n";
        $data['text'] .= "<b>Used Memory</b>: <code>$memUsed GiB</code>\n";
        $data['text'] .= "<b>Memory Usage</b>: <code>$memUsage%</code>\n";
        $uptime = $this->getUptime();
        $data['text'] .= "<b>Uptime</b>: <code>$uptime</code>\n";
        $this->dispatch(new SendMessageJob($data));
    }

    /**
     * @return int
     */
    private function getCpuNums(): int
    {
        $cpuNums = 0;
        $cpuInfo = file_get_contents('/proc/cpuinfo');
        $cpuInfo = explode("\n", $cpuInfo);
        foreach ($cpuInfo as $line) {
            if (str_starts_with($line, 'processor')) {
                $cpuNums++;
            }
        }
        return $cpuNums;
    }

    /**
     * @param string $stat
     * @return array
     */
    private function getCpuStat(string $stat): array
    {
        $stat = explode("\n", $stat);
        $line = $stat[0];
        $assigned = sscanf(
            $line,
            'cpu  %d %d %d %d %d %d %d %d %d %d',
            $user, $nice, $system, $idle, $ioWait, $irq, $softIrq, $steal, $guest, $guestnice
        );
        if ($assigned == 10) {
            return compact('user', 'nice', 'system', 'idle', 'ioWait', 'irq', 'softIrq', 'steal', 'guest', 'guestnice');
        }
        return [];
    }

    /**
     * @param array $times1
     * @param array $times2
     * @return float
     */
    private function getCpuUsage(array $times1, array $times2): float
    {
        $times1['user'] -= $times1['guest'];
        $times1['nice'] -= $times1['guestnice'];
        $times2['user'] -= $times2['guest'];
        $times2['nice'] -= $times2['guestnice'];
        $stat = [
            'idleAllTime' =>
                $times2['idle']
                + $times2['ioWait']
                - $times1['idle']
                - $times1['ioWait'],
            'totalTime' =>
                $times2['user']
                + $times2['nice']
                + $times2['steal']
                + $times2['system']
                + $times2['irq']
                + $times2['softIrq']
                + $times2['idle']
                + $times2['ioWait']
                + $times2['guest']
                + $times2['guestnice']
                - $times1['user']
                - $times1['nice']
                - $times1['steal']
                - $times1['system']
                - $times1['irq']
                - $times1['softIrq']
                - $times1['idle']
                - $times1['ioWait']
                - $times1['guest']
                - $times1['guestnice'],
        ];
        return number_format(100 * ($stat['totalTime'] - $stat['idleAllTime']) / $stat['totalTime'], 2, '.', '');
    }

    /**
     * @param array $memInfo
     * @return int
     */
    private function getMemTotal(array $memInfo): int
    {
        foreach ($memInfo as $line) {
            if (str_starts_with($line, 'MemTotal')) {
                if (preg_match('/\d+/', $line, $matches)) {
                    return (int)$matches[0];
                }
            }
        }
        return 0;
    }

    /**
     * @param array $memInfo
     * @return int
     */
    private function getMemFree(array $memInfo): int
    {
        foreach ($memInfo as $line) {
            if (str_starts_with($line, 'MemFree')) {
                if (preg_match('/\d+/', $line, $matches)) {
                    return (int)$matches[0];
                }
            }
        }
        return 0;
    }

    /**
     * @param array $memInfo
     * @return int
     */
    private function getMemAvailable(array $memInfo): int
    {
        foreach ($memInfo as $line) {
            if (str_starts_with($line, 'MemAvailable')) {
                if (preg_match('/\d+/', $line, $matches)) {
                    return (int)$matches[0];
                }
            }
        }
        return 0;
    }

    /**
     * @return string
     */
    private function getUptime(): string
    {
        $uptimes = file_get_contents('/proc/uptime');
        $uptimes = explode(' ', $uptimes);
        $uptimes = $uptimes[0];
        $uptimes = explode('.', $uptimes);
        $uptime = $uptimes[0];
        $milliseconds = $uptimes[1];
        $days = floor($uptime / 86400);
        $uptime %= 86400;
        $hours = floor($uptime / 3600);
        $uptime %= 3600;
        $minutes = floor($uptime / 60);
        $seconds = $uptime % 60;
        return "$days:$hours:$minutes:$seconds.$milliseconds";
    }
}
