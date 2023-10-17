<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace App\Common\Cache;

use App\Jobs\DeleteCache;
use Illuminate\Support\Facades\Redis;

/**
 * @see CacheKeyEnum
 */
class HashKey
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

    public function delete(string $hashKey = null, string ...$hashKeys): bool|int
    {
        if ($hashKey) {
            return $this->connection->hDel($this->fullKey, $hashKey, ...$hashKeys);
        }
        DeleteCache::dispatch($this->fullKey);
        return $this->connection->del($this->fullKey);
    }

    public function has(string $hashKey = null): bool
    {
        if ($hashKey) {
            return $this->connection->hExists($this->fullKey, $hashKey);
        }
        return $this->connection->exists($this->fullKey);
    }

    public function get(string $hashKey = null): mixed
    {
        if ($hashKey) {
            return $this->unserialize($this->connection->hGet($this->fullKey, $hashKey));
        } else {
            $data = $this->connection->hGetAll($this->fullKey) ?: [];
            $result = [];
            foreach ($data as $hashKey => $value) {
                $result[$hashKey] = $this->unserialize($value);
            }
            return $result;
        }
    }

    public function set(string $hashKey, $value, int $ttl): bool|int
    {
        $result = $this->connection->hSet($this->fullKey, $hashKey, $this->serialize($value));
        if ($ttl) {
            $this->connection->expire($this->fullKey, $ttl);
        }
        return $result;
    }

    public function count(): bool|int
    {
        return $this->connection->hLen($this->fullKey);
    }

    public function keys(): bool|array
    {
        return $this->connection->hKeys($this->fullKey);
    }

    public function values(): bool|array
    {
        return array_map(fn($v): mixed => $this->unserialize($v), $this->connection->hVals($this->fullKey));
    }
}
