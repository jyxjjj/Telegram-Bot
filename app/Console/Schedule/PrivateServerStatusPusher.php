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
            $this->info('Start to run the server status monitor');
            $icmpServerLists = env('MONITORING_SERVER_LISTS_ICMP', '');
            $icmpServerLists = explode(',', $icmpServerLists);
            $errs = $this->detect($icmpServerLists);
            if (count($errs) > 0) {
                $this->pushToOwner($errs);
            }
            $this->info('Finish to run the server status monitor');
            return self::SUCCESS;
        } catch (Throwable $e) {
            Handler::logError($e);
            $this->error("Error when running the server status monitor: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * @param array $icmpServerLists
     * @param array $tcpServerLists
     * @param array $udpServerLists
     * @param array $httpServerLists
     * @return array
     */
    private function detect(array $icmpServerLists): array
    {
        $errs = [];
        $this->info('Start ICMP detection');
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
     * @return float
     */
    private function ping(string $host): string
    {
        try {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_SOCKET);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 1, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
            $loss = 0;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_sendto($socket, "\x08\x00\x19\x2f\x00\x00\x00\x00PingHost", 16, 0, $host, 0) && socket_recv($socket, $recv, 255, 0) || $loss++;
            socket_close($socket);
        } catch (Throwable $e) {
            return "Error: {$e->getMessage()}";
        }
        $rate = number_format($loss / 8 * 100, 2, '.', '');
        return $rate >= 37.5 ? "Packet loss rate: {$rate} >= 37.5%" : "";
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
                $this->info("Pushing to $user");
                $message = [
                    'chat_id' => $user,
                    'text' => $text,
                ];
                $this->dispatch(new SendMessageJob($message, null, 300));
                if ($user != end($to)) {
                    $this->info('Sleep for 5 seconds');
                    sleep(5);
                }
            }
        }
    }
}
