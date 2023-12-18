<?php

namespace App\Exceptions;

use App\Common\ERR;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Throwable;

//use Illuminate\Http\Client\ConnectionException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        CommandNotFoundException::class,
//        ConnectionException::class,
    ];
    protected $dontFlash = [
    ];

    public function register(): void
    {
        $this->reportable(
            function (Throwable $e) {
                ERR::log($e);
                return false;
            }
        )->stop();
        $this->renderable(
            function (Throwable $e) {
            }
        );
    }
}
