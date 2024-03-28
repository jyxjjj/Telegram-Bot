<?php

namespace App\Jobs;

use App\Console\Commands\AutoPass;
use App\Jobs\Base\BaseQueue;

class AutoPassJob extends BaseQueue
{
    public function handle(): void
    {
        (new AutoPass)->handle();
    }
}
