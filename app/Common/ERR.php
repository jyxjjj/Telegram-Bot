<?php

namespace App\Common;

use Illuminate\Support\Facades\Log;
use Throwable;

class ERR
{
    final public static function log(Throwable $e, array $context = []): void
    {
        try {
            $context[] = self::getTraceAsString(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
            foreach ($e->getTrace() as $caller) {
                $context[] = self::getTraceAsString($caller);
            }
        } catch (Throwable $e) {
        }
        Log::error(
            self::getErrorAsString($e),
            $context
        );
    }

    final public static function getTraceAsString(array $oneTrace): string
    {
        [$class, $type, $function, $file, $line] = [$oneTrace['class'] ?? 'UnknownClass', $oneTrace['type'] ?? '::', $oneTrace['function'] ?? 'UnknownFunction', $oneTrace['file'] ?? 'UnknownFile', $oneTrace['line'] ?? 0];
        return sprintf("%s%s%s@%s:%d", $class, $type, $function, $file, $line);
    }

    final public static function getErrorAsString(Throwable $e): string
    {
        return sprintf("[%s(%d):%s]@[%s:%s]", $e::class, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
    }
}
