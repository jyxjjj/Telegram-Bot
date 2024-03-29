<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
 *
 * Released under GNU General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Models\BaseModel;
use Illuminate\Console\Command;
use ReflectionClass;
use Throwable;

class ColumnToModelDoc extends Command
{
    protected $signature = 'model:doc';
    protected $description = 'Generate model doc from database column.';

    public function handle(): int
    {
        $path = app_path('Models');
        $files = glob("$path/*.php");
        $files = array_filter($files, function ($file) {
            return !str_contains($file, 'BaseModel.php');
        });
        foreach ($files as $file) {
            $model = str_replace('.php', '', $file);
            $model = str_replace(base_path(), '', $model);
            $model = str_replace('/', '\\', $model);
            $model = str_replace('app', 'App', $model);
            try {
                $reflectionClass = new ReflectionClass($model);
                /** @var BaseModel $classObject */
                $classObject = $reflectionClass->newInstance();
            } catch (Throwable) {
                continue;
            }
            $columns = $classObject->getConnection()->getSchemaBuilder()->getColumns($classObject->getTable());
            $doc = $this->generateDoc($columns);
            dump(sprintf("% 32s: %16s %-32s", $reflectionClass->getShortName(), $classObject->getConnection()->getDatabaseName(), $classObject->getTable()));
            $content = file_get_contents($reflectionClass->getFileName());
            $comment = $reflectionClass->getDocComment();
            if ($comment) {
                $content = str_replace($comment, $doc, $content);
            } else {
                $className = $reflectionClass->getShortName();
                $extends = $reflectionClass->getParentClass()->getShortName();
                $line = "\nclass $className extends $extends\n";
                $content = str_replace($line, "\n$doc$line", $content);
            }
            file_put_contents($reflectionClass->getFileName(), $content);
        }
        return self::SUCCESS;
    }

    private function generateDoc(array $columns): string
    {
        $str = '/**' . PHP_EOL;
        foreach ($columns as $column) {
            $type = $column['type_name'];
            $this->resolveType($type);
            $name = $column['name'];
            $comment = $column['comment'] ?? $column['name'];
            $nullable = $column['nullable'] ? '?' : '';
            $str .= " * @property $nullable$type \$$name $comment\n";
        }
        $str .= ' */';
        return $str;
    }

    private function resolveType(string &$type): void
    {
        $type = match ($type) {
            'bigint', 'int', 'smallint', 'timestamp', 'tinyint' => 'int',
            'decimal', 'double', 'float' => 'float',
            default => 'string',
        };
    }
}
