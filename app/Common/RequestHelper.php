<?php

namespace App\Common;

use DESMG\RFC8942\RequestHeader;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RequestHelper
{
    /**
     * @param int $connectTimeout
     * @param int $timeout
     * @param int $retry
     * @param int $retryDelay
     * @param array $options
     * @return PendingRequest
     */
    public static function getInstance(int $connectTimeout = 5, int $timeout = 5, int $retry = 3, int $retryDelay = 1000, array $options = []): PendingRequest
    {
        return Http
            ::withHeaders(
                new RequestHeader(
                    '2.3',
                    6,
                    40,
                    130
                )->getCURLHeaders()
            )
            ->
            withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->withOptions($options)
            ->connectTimeout($connectTimeout)
            ->timeout($timeout)
            ->retry($retry, $retryDelay, throw: false);
    }
}
