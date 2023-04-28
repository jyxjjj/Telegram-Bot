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

    public function register(): void
    {
        $this->reportable(
            function (Throwable $e) {
                self::logError($e, __FILE__, __LINE__);
                return false;
            }
        );
        $this->renderable(
            function (Throwable $e) {
            }
        );
    }

    final public static function logError(Throwable $e, string $file, string $line): void
    {
        Log::error(self::getErrAsString($e), [get_class($e), $file, $line, $e->getTrace()]);
    }

    final public static function getErrAsString(Throwable $e): string
    {
        return "[{$e->getCode()}:{$e->getMessage()}]@[{$e->getFile()}:{$e->getLine()}]";
    }
}
