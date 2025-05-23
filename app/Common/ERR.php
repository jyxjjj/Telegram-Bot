<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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
