<?php

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
