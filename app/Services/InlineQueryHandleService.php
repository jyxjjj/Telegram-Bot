<?php

namespace App\Services;

use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;

class InlineQueryHandleService extends BaseService
{
    /**
     * @var array
     */
    private array $handlers;

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function handle(Update $update, Telegram $telegram, int $updateId): void
    {
        $query = $update->getInlineQuery();
    }
}
