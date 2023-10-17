<?php

namespace App\Common\Cache;

/**
 * <p>$cache = CacheKeyEnum::{ENUM_CASE}->newInstance($id);</p>
 * <p>$data = $cache->remember(function () use ($id) { ...return }, $ttl);</p>
 * <p>$cache->delete();</p>
 * <p>$cache->has();</p>
 * <p>$cache->get($default);</p>
 * <p>$cache->set($value, $ttl);</p>
 * <p>$cacheKey = $cache->getKey();</p>
 * <p>TRUE: $this == $cache->getKeyEnum();</p>
 * @see CacheKey
 */
enum CacheKeyEnum: string
{
    const MINUTE_1 = 60;
    const MINUTE_5 = 300;
    const MINUTE_10 = 600;
    const MINUTE_15 = 900;
    const MINUTE_30 = 1800;
    const HOUR_1 = 3600;
    const HOUR_2 = 7200;
    const HOUR_12 = 43200;
    const DAY_1 = 86400;
    const DAY_2 = 172800;
    const DAY_7 = 604800;
    const DAY_30 = 2592000;
    const DAY_60 = 5184000;
    const DAY_90 = 7776000;
    const DAY_180 = 15552000;
    const DAY_365 = 31536000;

    /**
     * @param int|string ...$args
     * @return CacheKey
     */
    public function newCacheInstance(int|string ...$args): CacheKey
    {
        return new CacheKey($this, ...$args);
    }

    /**
     * @param int|string ...$args
     * @return string
     */
    public function getKey(int|string ...$args): string
    {
        return vsprintf($this->value, $args);
    }
}
