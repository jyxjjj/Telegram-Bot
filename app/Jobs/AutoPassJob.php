<?php

namespace App\Jobs;

use App\Console\Commands\AutoPass;
use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class AutoPassJob extends BaseQueue
{
    public function handle(): void
    {
        if (Cache::has('autopass')) {
            return;
        }
        Cache::set('autopass', 1, 3600);
        Artisan::call(AutoPass::class);
        $data = [
            'chat_id' => env('YPP_SOURCE_ID'),
            'text' => '[SUCCESS]全部自动通过处理完成',
        ];
        SendMessageJob::dispatch($data, null, 0);
        Cache::delete('autopass');
    }
}
