<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        '\Symfony\Component\Console\Exception\CommandNotFoundException',
    ];
    protected $dontFlash = [
    ];

    public static function logError(Throwable $e)
    {
        Log::error($e->getMessage(), [$e->getFile(), $e->getLine(), $e->getTrace()]);
    }

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            \Sentry\captureException($e);
        });
        $this->renderable(function (Throwable $e) {
        });
    }
}
