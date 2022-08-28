<?php

namespace App\Console\Schedule;

use Illuminate\Console\Command;
use Throwable;

class LogClean extends Command
{
    protected $signature = 'log:clean {preserve}';
    protected $description = 'Delete old logs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $preserve = (int)$this->argument('preserve');
            if ($preserve < 3 && env('APP_ENV') != 'local') {
                self::error('保留日志天数过少，删除请求被拒绝。');
                return self::INVALID;
            }
            $whiteLists = $this->generateWhiteLists($preserve);
            $path = storage_path('logs');
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $in = in_array("$path/$file", $whiteLists, true);
                    if (!$in) {
                        self::warn("File: $path/$file is not in White List, deleting...");
                        unlink("$path/$file");
                        self::warn("Deleted: $path/$file .");
                    } else {
                        self::info("$path/$file is in White List, skipping...");
                    }
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function generateWhiteLists(int $int): array
    {
        $arr = [
            storage_path('logs/schedule.log'),
            storage_path('logs/default.queue.out.log'),
            storage_path('logs/TelegramLimitedApiRequest.queue.out.log'),
        ];
        for ($i = 0; $i > -$int; $i--) {
            $filename = date('Y-m-d', strtotime("$i days"));
            $filename = storage_path("logs/$filename");
            $arr[] = $filename . '.single.log';
            $arr[] = $filename . '.sql.log';
            $arr[] = $filename . '.perf.log';
            $arr[] = $filename . '.deprecations.log';
            $arr[] = $filename . '.emergency.log';
        }
        foreach ($arr as $file) {
            $this->line("WhiteList: $file");
        }
        return $arr;
    }
}
