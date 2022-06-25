<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        '\Symfony\Component\Console\Exception\CommandNotFoundException',
    ];
    protected $dontFlash = [
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
        });
        $this->renderable(function (Throwable $e) {
        });
    }
}
