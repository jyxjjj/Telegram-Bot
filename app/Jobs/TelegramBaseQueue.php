<?php

namespace App\Jobs;

use Illuminate\Queue\Middleware\RateLimitedWithRedis;

class TelegramBaseQueue extends BaseQueue
{
    public function __construct()
    {
        parent::__construct();
        $this->onQueue('TelegramLimitedApiRequest');
    }

    public function middleware(): array
    {
        return [new RateLimitedWithRedis('TelegramLimitedApiRequest')];
    }
}
