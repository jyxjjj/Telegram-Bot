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
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Common\Texts;

/**
 * 混淆文本标准化与关键词匹配。
 */
final class ObfuscatedTextNormalizer
{
    /**
     * 依次使用标准文本、文本骨架和有限字符间隔匹配关键词。
     */
    public static function matches(string $value, string $keyword): bool
    {
        $value = self::canonicalize($value);
        $keyword = self::canonicalize($keyword);
        if ($keyword === '') {
            return false;
        }
        if (str_contains($value, $keyword)) {
            return true;
        }

        $valueSkeleton = self::skeletonize($value);
        $keywordSkeleton = self::skeletonize($keyword);
        if (mb_strlen($keywordSkeleton, 'UTF-8') < 2) {
            return false;
        }
        if (str_contains($valueSkeleton, $keywordSkeleton)) {
            return true;
        }

        $keywordCharacters = preg_split('//u', $keywordSkeleton, -1, PREG_SPLIT_NO_EMPTY);
        if ($keywordCharacters === false) {
            return false;
        }

        // 相邻关键词字符之间最多允许三个可见字符。
        $pattern = '~' . implode(
                '.{0,3}',
                array_map(static fn(string $character): string => preg_quote($character, '~'), $keywordCharacters)
            ) . '~us';

        return preg_match($pattern, $valueSkeleton) === 1;
    }

    /**
     * 转换全角字符、统一大小写并移除 Unicode 控制字符。
     */
    public static function canonicalize(string $value): string
    {
        // 全角空格和全角 ASCII 转半角。
        $value = preg_replace_callback(
            '/[\x{3000}\x{FF01}-\x{FF5E}]/u',
            static fn(array $matches): string => $matches[0] === "　"
                ? ' '
                : mb_chr(mb_ord($matches[0], 'UTF-8') - 0xFEE0, 'UTF-8'),
            $value
        ) ?? $value;

        $value = mb_strtoupper($value, 'UTF-8');

        return preg_replace('/\p{C}+/u', '', $value) ?? $value;
    }

    /**
     * 移除 Unicode 标记、标点、符号和分隔符，生成匹配骨架。
     */
    public static function skeletonize(string $value): string
    {
        $value = self::canonicalize($value);

        return preg_replace('/[\p{M}\p{P}\p{S}\p{Z}]+/u', '', $value) ?? $value;
    }
}
