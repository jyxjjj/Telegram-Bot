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
                self::logError($e);
                return false;
            }
        );
        $this->renderable(
            function (Throwable $e) {
            }
        );
    }

    final public static function logError(Throwable $e, array $context = []): void
    {
        function getTraceAsString(array $oneTrace): string
        {
            $class = $oneTrace['class'] ?? 'UnknownClass';
            $type = $oneTrace['type'] ?? '::';
            $function = $oneTrace['function'] ?? 'UnknownFunction';
            $file = $oneTrace['file'] ?? 'UnknownFile';
            $line = $oneTrace['line'] ?? 0;
            return sprintf("%s%s%s@%s:%d", $class, $type, $function, $file, $line);
        }

        try {
            $context[] = getTraceAsString(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
            foreach ($e->getTrace() as $caller) {
                $context[] = getTraceAsString($caller);
            }
        } catch (Throwable $e) {
        }
        Log::error(sprintf("[%s(%d):%s]@[%s:%s]", $e::class, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine()), $context);
    }
}
