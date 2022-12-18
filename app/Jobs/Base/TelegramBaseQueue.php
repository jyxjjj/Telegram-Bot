<?php

namespace App\Jobs\Base;

use Illuminate\Queue\Middleware\RateLimitedWithRedis;

class TelegramBaseQueue extends BaseQueue
{

    public int $tries = 30;

    public function __construct()
    {
        parent::__construct();
        $this->onQueue('TelegramLimitedApiRequest');
    }

    public function middleware(): array
    {
        return [new RateLimitedWithRedis('TelegramLimitedApiRequest')];
    }

    /**
     * @return int[]
     */
    public function backoff(): array
    {
        return [10, 15, 30];
    }
}
