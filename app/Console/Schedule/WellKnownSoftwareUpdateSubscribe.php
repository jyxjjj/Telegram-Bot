<?php

namespace App\Console\Schedule;

use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
use App\Exceptions\Handler;
use App\Jobs\SendMessageJob;
use App\Models\TUpdateSubscribes;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Throwable;

class WellKnownSoftwareUpdateSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:update';
    protected $description = 'Get the Newest Version then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            foreach (Software::cases() as $software) {
                self::info("Checking $software->name...");
                if (!in_array($software->name, [
                    Software::PHP->name,
                    Software::Nginx->name,
                    Software::MariaDB->name,
                    Software::MariaDBDocker->name,
                    Software::Redis->name,
                    Software::RedisDocker->name,
                    Software::NodeJS->name,
                    Software::Kernel->name,
                    Software::OpenSSL->name,
                    Software::Laravel->name,
                    Software::VSCode->name,
                ])) {
                    continue;
                }
                try {
                    /** @var TUpdateSubscribes[] $datas */
                    $datas = TUpdateSubscribes::getAllSubscribeBySoftware($software->name);
                    foreach ($datas as $data) {
                        $chat_id = $data['chat_id'];
                        $version = $software->getInstance()->getVersion();
                        $lastVersion = Common::getLastVersion($software);
                        self::info("$software->name Current:$version Latest:$lastVersion");
                        if ($version && $lastVersion != $version) {
                            $message = $software->getInstance()->generateMessage($chat_id, $version);
                            $this->dispatch(new SendMessageJob($message, null, 0));
                            Common::setLastVersion($software, $version);
                        }
                    }
                } catch (Throwable $e) {
                    Handler::logError($e);
                    continue;
                }
                sleep(1);
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            Handler::logError($e);
            return self::FAILURE;
        }
    }
}
