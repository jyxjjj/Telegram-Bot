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

    function getLang()
    {
        return 'en';
    }

    function getLangs()
    {
        return ['en'];
    }

    function getLangsAll()
    {
        return ['en'];
    }

    function getLangsAllWithNames()
    {
        return ['en' => 'English'];
    }

    function getLangsWithNames()
    {
        return ['en' => 'English'];
    }

    function getLangsWithNamesAll()
    {
        return ['en' => 'English'];
    }

    function getLangsWithNamesAllWithDefault()
    {
        return ['en' => 'English'];
    }
}
