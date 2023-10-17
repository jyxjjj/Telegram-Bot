<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace App\Common\Cache;

use App\Jobs\DeleteCache;
use Illuminate\Support\Facades\Redis;

/**
 * @see CacheKeyEnum
 */
class SortedSet
{
    use CacheSerializable, CacheRedisHelper;

    private \Redis $connection;
    private string $prefix;
    private string $key;
    private string $fullKey;

    public function __construct(CacheKeyEnum $key, int|string ...$v)
    {
        $this->connection = Redis::connection('cache')->client();
        $this->prefix = config('database.redis.options.prefix') . config('cache.prefix') . ':';
        $this->key = vsprintf($key->value, $v);
        $this->fullKey = $this->prefix . $this->key;
    }

    public function set($setKey, int|float $score, int $ttl): bool|int
    {
        $result = $this->connection->zIncrBy($this->fullKey, $score, $this->serialize($setKey));
        if ($ttl) {
            $this->connection->expire($this->fullKey, $ttl);
        }
        return $result;
    }

    public function add($setKey, int|float $score, int $ttl): bool|int
    {
        $result = $this->connection->zIncrBy($this->fullKey, $score, $this->serialize($setKey));
        if ($ttl) {
            $this->connection->expire($this->fullKey, $ttl);
        }
        return $result;
    }

    public function delete($setKey = null, string ...$setKeys): bool|int
    {
        if ($setKey) {
            return $this->connection->zRem($this->fullKey, $this->serialize($setKey), ...array_map(fn($v): string => $this->serialize($v), $setKeys));
        }
        DeleteCache::dispatch($this->fullKey);
        return $this->connection->del($this->fullKey);
    }

    public function has($setKey = null): bool
    {
        if ($setKey) {
            return $this->connection->zScore($this->fullKey, $this->serialize($setKey)) !== false;
        }
        return $this->connection->exists($this->fullKey);
    }

    public function scoreBetween(int|float|string $min = '-inf', int|float|string $max = '+inf'): bool|array
    {
        $data = $this->connection->zRangeByScore($this->fullKey, $min, $max, ['WITHSCORES' => true,]) ?: [];
        $result = [];
        foreach ($data as $setKey => $score) {
            $result[$this->unserialize($setKey)] = $score;
        }
        return $result;
    }
}
