<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SetCache extends Command
{
    protected $signature = 'cache:set {key} {value}';
    protected $description = 'Set a value into the cache';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        self::info(Cache::put(self::argument('key'), self::argument('value')));
        return self::SUCCESS;
    }
}
