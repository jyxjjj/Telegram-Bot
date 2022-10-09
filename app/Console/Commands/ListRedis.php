<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Redis;

class ListRedis extends Command
{
    use ConfirmableTrait;

    protected $signature = 'cache:list {connection?} {pattern?}';
    protected $description = 'List all cache keys by pattern';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!$this->confirmToProceed()) {
            return self::FAILURE;
        }
        $connection = self::argument('connection') ?? 'default';
        $pattern = self::argument('pattern') ?? '*';
        $prefix = config('database.redis.options.prefix');
        self::info('Listing all cache keys with pattern: ' . $pattern);
        $keys = Redis::connection($connection)->command('KEYS', [$pattern]);
        sort($keys);
        $datas = [];
        foreach ($keys as $key) {
            if (!str_starts_with($key, $prefix)) continue;
            $datas[] = str_replace($prefix, '', $key);
        }
        self::table(
            ['KEY', 'VALUE'],
            array_map(
                fn($key) => [
                    $key, substr(Redis::connection($connection)->command('GET', [$key]), 0, 16)
                ],
                $datas
            )
        );
        return self::SUCCESS;
    }
}
