<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RemoveCache extends Command
{
    protected $signature = 'cache:remove {key}';
    protected $description = 'Remove a cache by key';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        self::info('Showing the data of the cache: ' . self::argument('key'));
        self::info(Cache::get(self::argument('key'), 'This key is empty.'));
        if (self::confirm('Do you really want to delete this')) {
            self::info(Cache::forget(self::argument('key')));
        }
        return self::SUCCESS;
    }
}
