<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ShowCache extends Command
{
    protected $signature = 'cache:show {key}';
    protected $description = 'Get the value of a cache key';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        self::info('Showing the data of the cache: ' . self::argument('key'));
        self::info(Cache::get(self::argument('key'), 'This key is empty.'));
        return self::SUCCESS;
    }
}
