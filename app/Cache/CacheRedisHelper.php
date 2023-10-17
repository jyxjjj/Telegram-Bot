<?php

namespace App\Common\Cache;

use Redis;

trait CacheRedisHelper
{
    public function getConnection(): ?Redis
    {
        return $this->connection ?? null;
    }

    public function getFullKey(): ?string
    {
        return $this->fullKey ?? null;
    }

    public function getKey(): ?string
    {
        return $this->key ?? null;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix ?? null;
    }
}
