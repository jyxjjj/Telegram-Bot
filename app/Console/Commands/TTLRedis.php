<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Redis;

class TTLRedis extends Command
{
    use ConfirmableTrait;

    protected $signature = 'cache:ttl {connection?} {key?}';
    protected $description = 'List ttl of key';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $connection = self::argument('connection') ?? 'default';
        $key = self::argument('key') ?? '*';
        self::info('Listing ttl of key: ' . $key);
        $ttl = Redis::connection($connection)->command('TTL', [$key]);
        self::info('TTL: ' . $ttl);
        return self::SUCCESS;
    }
}
