<?php

namespace App\Jobs;

use App\Exceptions\Handler;
use App\Jobs\Base\BaseQueue;
use App\Services\UpdateHandleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class WebhookJob extends BaseQueue
{
    private Update $update;
    private Telegram $telegram;
    private int $updateId;

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     */
    public function __construct(Update $update, Telegram $telegram, int $updateId)
    {
        parent::__construct();
        $this->update = $update;
        $this->telegram = $telegram;
        $this->updateId = $updateId;
    }

    public function handle(): void
    {
        $update = $this->update;
        $telegram = $this->telegram;
        $updateId = $this->updateId;
        try {
            /** @var UpdateHandleService $service */
            $service = app(UpdateHandleService::class);
            $service->handle($update, $telegram, $updateId);
        } catch (TelegramException|BindingResolutionException $e) {
            Handler::logError($e);
        }
    }
}
