<?php

namespace App\Jobs\Base;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BaseQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public int $tries = 5;
    /**
     * @var int
     */
    public int $maxExceptions = 3;

    public function __construct()
    {
    }

    public function backoff(): array
    {
        return [1, 5, 10];
    }
}
