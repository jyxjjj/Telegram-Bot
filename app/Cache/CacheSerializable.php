<?php

namespace App\Common\Cache;

trait CacheSerializable
{
    private function unserialize($value): mixed
    {
        return is_numeric($value) ? $value : unserialize($value);
    }

    private function serialize($value): float|int|string
    {
        return is_numeric($value) && !in_array($value, [INF, -INF]) && !is_nan($value) ? $value : serialize($value);
    }
}
