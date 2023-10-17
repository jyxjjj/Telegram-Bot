<?php

namespace App\Common\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ListKey
{
    use CacheSerializable, CacheRedisHelper;

    private \Redis $connection;
    private string $prefix;
    private string $key;
    private string $fullKey;

    public function __construct(CacheKeyEnum $key, int|string ...$v)
    {
        $this->connection = Redis::connection('cache')->client();
        $this->prefix = Cache::getPrefix();
        $this->key = vsprintf($key->value, $v);
        $this->fullKey = $this->prefix . $this->key;
    }
}
