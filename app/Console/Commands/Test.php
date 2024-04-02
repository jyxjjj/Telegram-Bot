<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Test extends Command
{
    protected $signature = 'test';
    protected $description = 'Test';

    public function handle(): int
    {
        Cache::delete('autopass');
        return self::SUCCESS;
    }
}
