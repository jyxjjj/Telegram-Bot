<?php

namespace App\Http\Services\Bots\Jobs;

use App\Jobs\BaseQueue;
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
