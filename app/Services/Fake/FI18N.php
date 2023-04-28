<?php

namespace App\Services\Fake;

use App\Services\Base\BaseService;

class FI18N extends BaseService
{
    function format($key, $params = [])
    {
        return $key;
    }

    function get($key, $params = [])
    {
        return $key;
    }

    function getLang(): string
    {
        return 'en';
    }

    function getLangs(): array
    {
        return ['en'];
    }

    function getLangsAll(): array
    {
        return ['en'];
    }

    function getLangsAllWithNames(): array
    {
        return ['en' => 'English'];
    }

    function getLangsWithNames(): array
    {
        return ['en' => 'English'];
    }

    function getLangsWithNamesAll(): array
    {
        return ['en' => 'English'];
    }

    function getLangsWithNamesAllWithDefault(): array
    {
        return ['en' => 'English'];
    }
}
