<?php

namespace App\Console\Schedule;

use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
use App\Exceptions\Handler;
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
//                $lastSend = $this->getLastSend($chat_id);
//                if (!$lastSend) {
//                    $this->dispatch(new SendMessageJob($message, null, 0));
//                    $this->setLastSend($chat_id, $version);
//                } else {
//                    if ($lastSend != $version) {
//                        $this->dispatch(new SendMessageJob($message, null, 0));
//                        $this->setLastSend($chat_id, $version);
//                    }
//                }
            foreach (Software::cases() as $software) {
                /** @var TUpdateSubscribes[] $datas */
                $datas = TUpdateSubscribes::getAllSubscribeBySoftware($software->name);
                foreach ($datas as $data) {
                    $chat_id = $data->chat_id;
//                    $version = $software->getVersion();
//                    $lastVersion = $this->getLastVersion($software);
//                    if ($lastVersion != $version) {
//                        $message = $software->getMessage($version);
//                        $this->dispatch(new SendMessageJob($message, null, 0));
//                        $this->setLastVersion($software, $version);
//                    }
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            Handler::logError($e);
            return self::FAILURE;
        }
    }
}
