<?php

namespace App\Console\Schedule;

use App\Jobs\SendMessageJob;
use App\Models\TUpdateSubscribes;
use Carbon\Carbon;
use DESMG\RFC6986\Hash;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Throwable;

class PhpUpdateSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:php';
    protected $description = 'Get PHP Newest Version then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            self::info('Start to get PHP newest versions');
            $updates = $this->getUpdate();
            self::info('Get PHP newest versions successfully');
            /** @var TUpdateSubscribes[] $datas */
            $datas = TUpdateSubscribes::getAllSubscribeBySoftware('PHP');
            self::info('Get all subscribers');
            foreach ($datas as $data) {
                $chat_id = $data->chat_id;
                self::info("Start to process {$chat_id}");
                $string = $this->getUpdateData($chat_id, $updates);
                $hash = Hash::sha512(str_replace(' (NEW)', '', $string));
                $message = [
                    'chat_id' => $chat_id,
                    'text' => $string,
                ];
                $lastSend = $this->getLastSend($chat_id);
                if (!$lastSend) {
                    self::info("Haven't send any update to {$chat_id}");
                    $this->dispatch(new SendMessageJob($message, null, 0));
                    $this->setLastSend($chat_id, $hash);
                    self::info("Send update to {$chat_id} successfully");
                } else {
                    if ($lastSend != $hash) {
                        $this->dispatch(new SendMessageJob($message, null, 0));
                        $this->setLastSend($chat_id, $hash);
                        self::info("Send update to {$chat_id} successfully");
                    } else {
                        self::info("No new update for {$chat_id}");
                    }
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            self::error($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @param int $chat_id
     * @return string|false
     */
    private function getLastSend(int $chat_id): string|false
    {
        return Cache::get("Schedule::UpdateSubscribe::last_send::{$chat_id}::PHP", false);
    }

    /**
     * @param int $chat_id
     * @param string $hash
     * @return bool
     */
    private function setLastSend(int $chat_id, string $hash): bool
    {
        return Cache::put("Schedule::UpdateSubscribe::last_send::{$chat_id}::PHP", $hash, Carbon::now()->addMonths(3));
    }
}
