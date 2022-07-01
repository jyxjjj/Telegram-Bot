<?php

namespace App\Http\Services\Commands;

use App\Http\Services\BaseCommand;
use App\Jobs\SendMessageJob;
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
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
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
        $data['text'] .= "*Num of Cores:* `$cpuNums`\n";
        $data['text'] .= "*CPU Usage:* `$cpuUsage%`\n";
        $memInfo = file_get_contents('/proc/meminfo');
        $memInfo = explode("\n", $memInfo);
        $memTotal = $this->getMemTotal($memInfo);
        $memFree = $this->getMemFree($memInfo);
        $memUsed = $memTotal - $memFree;
        $memUsage = number_format($memUsed / $memTotal * 100, 2, '.', '');
        $memTotal = number_format($memTotal / 1024 / 1024, 2, '.', '');
        $memFree = number_format($memFree / 1024 / 1024, 2, '.', '');
        $memUsed = number_format($memUsed / 1024 / 1024, 2, '.', '');
        $data['text'] .= "*Total Memory:* `$memTotal GiB`\n";
        $data['text'] .= "*Free Memory:* `$memFree GiB`\n";
        $data['text'] .= "*Used Memory:* `$memUsed GiB`\n";
        $data['text'] .= "*Memory Usage:* `$memUsage%`\n";
        $uptime = $this->getUptime();
        $data['text'] .= "*Uptime:* `$uptime`\n";
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
     * @return string
     */
    private function getUptime(): string
    {
        $uptimes = file_get_contents('/proc/uptime');
        $uptimes = explode(' ', $uptimes);
        $uptimes = $uptimes[0];
        $uptimes = explode('.', $uptimes);
        $uptime = $uptimes[0];
        $millseconds = $uptimes[1];
        $days = floor($uptime / 86400);
        $uptime %= 86400;
        $hours = floor($uptime / 3600);
        $uptime %= 3600;
        $minutes = floor($uptime / 60);
        $seconds = $uptime % 60;
        return "$days:$hours:$minutes:$seconds.$millseconds";
    }
}
