<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace App\Common\Cache;

use App\Jobs\DeleteCache;
use Illuminate\Support\Facades\Redis;

/**
 * @see CacheKeyEnum
 */
class SetKey
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

    public function delete($setKey = null, ...$setKeys): bool|int
    {
        if ($setKey) {
            return $this->connection->sRem($this->fullKey, $this->serialize($setKey), array_map(fn($v): string => $this->serialize($v), $setKeys));
        }
        DeleteCache::dispatch($this->fullKey);
        return $this->connection->del($this->fullKey);
    }

    public function has($setKey = null): bool
    {
        if ($setKey) {
            return $this->connection->sIsMember($this->fullKey, $this->serialize($setKey));
        }
        return $this->connection->exists($this->fullKey);
    }

    public function members(): bool|array
    {
        return array_map(fn($v): mixed => $this->unserialize($v), $this->connection->sMembers($this->fullKey));
    }

    public function set($setKey, int $ttl = null, ...$setKeys): bool|int
    {
        $result = $this->connection->sAdd($this->fullKey, $this->serialize($setKey), ...array_map(fn($v): string => $this->serialize($v), $setKeys));
        if ($ttl) {
            $this->connection->expire($this->fullKey, $ttl);
        }
        return $result;
    }

    public function count(): bool|int
    {
        return $this->connection->sCard($this->fullKey);
    }

    public function inter(self $targetSet): bool|array
    {
        return array_map(fn($v): mixed => $this->unserialize($v), $this->connection->sInter($this->fullKey, $targetSet->getFullKey()));
    }

    public function union(self $targetSet): bool|array
    {
        return array_map(fn($v): mixed => $this->unserialize($v), $this->connection->sUnion($this->fullKey, $targetSet->getFullKey()));
    }

    public function diff(self $targetSet): bool|array
    {
        return array_map(fn($v): mixed => $this->unserialize($v), $this->connection->sDiff($this->fullKey, $targetSet->getFullKey()));
    }
}
