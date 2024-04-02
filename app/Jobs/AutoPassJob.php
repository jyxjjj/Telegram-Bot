<?php

namespace App\Jobs;

use App\Console\Commands\AutoPass;
use App\Exceptions\Handler;
use App\Jobs\Base\BaseQueue;
use Throwable;

class AutoPassJob extends BaseQueue
{
    public function handle(): void
    {
        try {
            (new AutoPass)->handle();
        } catch (Throwable $e) {
            Handler::logError($e, __FILE__, __LINE__);
        }
    }
}
