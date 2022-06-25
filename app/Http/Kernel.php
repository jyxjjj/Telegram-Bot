<?php

namespace App\Http;

use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;

class Kernel extends HttpKernel
{
    protected $middleware = [
        EncryptCookies::class,
        StartSession::class,
        TrustHosts::class,
        TrustProxies::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];
    protected $middlewarePriority = [
        EncryptCookies::class,
        StartSession::class,
        TrustHosts::class,
        TrustProxies::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];
    protected $middlewareGroups = [
        'api' => [
            SubstituteBindings::class,
        ],
    ];
    protected $routeMiddleware = [
    ];
}
