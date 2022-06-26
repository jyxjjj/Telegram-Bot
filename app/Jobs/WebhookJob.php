<?php

namespace App\Jobs;

use App\Common\Client;
use App\Http\Services\UpdateHandleService;
use Carbon\Carbon;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;

class WebhookJob extends BaseQueue
{
    private array $data;
    private Carbon $now;

    public function __construct(array $data, Carbon $now)
    {
        parent::__construct();
        $this->data = $data;
        $this->now = $now;
    }

    /**
     * @throws TelegramException
     */
    public function handle()
    {
        $data = $this->data;
        $telegram = Client::getTelegram();
        $telegram->enableAdmin(env('TELEGRAM_ADMIN_USER_ID'));
        $telegram->setDownloadPath(storage_path('app/telegram'));
        $telegram->setUploadPath(storage_path('app/telegram'));
        $telegram->setCommandsPath(app_path('Http/Services/Commands'));
        $update = new Update($data, $telegram->getBotUsername());
        define('COMMAND_START', $this->now->getTimestampMs());
        $telegram->processUpdate($update);
        UpdateHandleService::handle($update, $telegram);
    }
}
