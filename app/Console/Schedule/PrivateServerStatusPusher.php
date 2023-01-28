<?php

namespace App\Console\Schedule;

use App\Exceptions\Handler;
use App\Jobs\SendMessageJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

class PrivateServerStatusPusher extends Command
{
    use DispatchesJobs;

    protected $signature = 'serverstatuspusher';
    protected $description = 'test the server status and push to the user if the server is down';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            self::info('Start to run the server status monitor');
            $icmpServerLists = env('MONITORING_SERVER_LISTS_ICMP', '');
            $icmpServerLists = explode(',', $icmpServerLists);
            $errs = $this->detect($icmpServerLists);
            if (count($errs) > 0) {
                $this->pushToOwner($errs);
            }
            self::info('Finish to run the server status monitor');
            return self::SUCCESS;
        } catch (Throwable $e) {
            Handler::logError($e);
            self::info("Error when running the server status monitor: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * @param array $icmpServerLists
     * @return array
     */
    private function detect(array $icmpServerLists): array
    {
        $errs = [];
        self::info('Start ICMP detection');
        foreach ($icmpServerLists as $icmpServer) {
            if (filter_var($icmpServer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE)) {
                $ip = $icmpServer;
            } else if (filter_var($icmpServer, FILTER_VALIDATE_DOMAIN)) {
                $ip = gethostbyname($icmpServer);
                if ($ip === $icmpServer) {
                    $errs[$icmpServer] = 'DNS Resolve Failed';
                    continue;
                }
            } else {
                continue;
            }
            $ping = $this->ping($ip);
            if ($ping) {
                $errs[$icmpServer] = $ping;
            }
        }
        return $errs;
    }

    /**
     * @param string $host
     * @return string
     */
    private function ping(string $host): string
    {
        function getCheckSum(string $package): string
        {
            strlen($package) % 2 && $package .= "\x00";
            $sum = array_sum(unpack('n*', $package));
            while ($sum >> 16) {
                $sum = ($sum >> 16) + ($sum & 0xffff);
            }
            return pack('n*', ~$sum);
        }

        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_SOCKET);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 1, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
            $loss = 0;
            for ($i = 0; $i < 16; $i++) {
                $start = Carbon::now()->getPreciseTimestamp();
                $checksum = hex2bin('0000');
                $seq_number = hex2bin(str_pad($i, 4, '0', STR_PAD_LEFT));
                $package = hex2bin('0800') . $checksum . hex2bin('0000') . $seq_number . 'PingHost';
                $checksum = getCheckSum($package);
                $package = hex2bin('0800') . $checksum . hex2bin('0000') . $seq_number . 'PingHost';
                socket_sendto($socket, $package, strlen($package), 0, $host, 0);
                $len = socket_recv($socket, $buffer, 256, 0);
                !$len && $loss++;
                $end = Carbon::now()->getPreciseTimestamp();
                $time = $end - $start;
                $time < 1000000 && usleep(1000000 - $time);
                $len && self::info(sprintf("%s %s %s %s ms", $host, bin2hex($package), bin2hex($buffer), number_format($time / 1000, 3, '.', '')));
            }
            socket_close($socket);
        } catch (Throwable $e) {
            self::error($e->getMessage());
            return "$host: Error: {$e->getMessage()}";
        }
        $rate = number_format($loss / 16 * 100, 2, '.', '');
        return $rate >= 87.5 ? "$host: Packet loss rate: $rate >= 87.5%" : "";
    }

    /**
     * @param array $errs
     * @return void
     */
    private function pushToOwner(array $errs): void
    {
        $text = "⚠️ Server abnormal occurred ⚠️\n";
        $count = 0;
        foreach ($errs as $ip) {
            if (Cache::has("PrivateServerStatusPusher::$ip")) {
                continue;
            }
            $time = Carbon::now();
            Cache::put("PrivateServerStatusPusher::$ip", $time->clone()->getTimestampMs(), $time->clone()->addMinutes(10));
            $text .= "<code>$ip</code>\n";
            $count++;
        }
        if ($count == 0) {
            return;
        }
        $to = env('MONITORING_SENDTO_USERS');
        $to = explode(',', $to);
        foreach ($to as $user) {
            if (filter_var($user, FILTER_VALIDATE_INT)) {
                self::info("Pushing to $user");
                $message = [
                    'chat_id' => $user,
                    'text' => $text,
                ];
                $this->dispatch(new SendMessageJob($message, null, 300));
                if ($user != end($to)) {
                    self::info('Sleep for 5 seconds');
                    sleep(5);
                }
            }
        }
    }
}
