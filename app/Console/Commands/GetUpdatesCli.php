<?php

namespace App\Console\Commands;

use App\Http\Services\Bots\BotCommon;
use App\Http\Services\Bots\UpdateHandleService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetUpdatesCli extends Command
{
    protected $signature = 'telegram:run';
    protected $description = 'Get updates from Telegram';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $botCommon = new BotCommon;
            $telegram = $botCommon->newTelegram();
            while (true) {
                try {
                    $updates = $botCommon->getUpdates([
                        'allowed_updates' => [
                            'message',
                            'edited_message',
                            'channel_post',
                            'edited_channel_post',
                            'chat_member',
                            'my_chat_member',
                            'chat_join_request',
                        ],
                    ]);
                    foreach ($updates as $update) {
                        UpdateHandleService::handle($update, $telegram);
                    }
                    usleep(500 * 1000);
                } catch (Exception $e) {
                    $this->logError($e);
                    try {
                        $botCommon->enableMysql();
                    } catch (Exception $e) {
                        $this->logError($e);
                    }
                }
            }
        } catch (Exception $e) {
            $this->logError($e);
        }
        return self::SUCCESS;
    }

    private function logError(Exception $e)
    {
        self::error($e->getMessage());
        Log::error($e->getMessage(), [$e->getFile(), $e->getLine()]);
    }
}
