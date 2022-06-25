<?php

namespace App\Console\Schedule;

use Exception;
use Illuminate\Console\Command;

class LogClean extends Command
{
    protected $signature = 'command:logClean {preserve}';
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
                $this->error('Preserved logs\' days too less, must be greater than 3');
                return self::INVALID;
            }
            $whiteLists = $this->generateWhiteLists($preserve);
            $path = storage_path('logs');
            $dirs = scandir($path);
            foreach ($dirs as $dir) {
                if (is_dir("$path/$dir") && $dir != '.' && $dir != '..') {
                    $files = scandir("$path/$dir");
                    foreach ($files as $file) {
                        if ($file != '.' && $file != '..') {
                            $in = in_array("$path/$dir/$file", $whiteLists, true);
                            if (!$in) {
                                $this->warn("File: $path/$dir/$file is not in White List, deleting...");
                                unlink("$path/$dir/$file");
                                $this->warn("Deleted: $path/$dir/$file .");
                            } else {
                                $this->info("$path/$dir/$file is in White List, skipping...");
                            }
                        }
                    }
                    $files = scandir("$path/$dir");
                    if (count($files) === 2) {
                        $this->alert("File: $path/$dir is empty, deleting...");
                        rmdir("$path/$dir");
                    }
                }
            }
            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            //            $code = $e->getCode();
            //            if ($code == 0) {
            //                $code = -1;
            //            }
            return self::FAILURE;
        }
    }

    private function generateWhiteLists(int $int): array
    {
        $arr = [];
        for ($i = 0; $i > -$int; $i--) {
            $filename = date('Y-m/d', strtotime("$i days"));
            $filename = storage_path("logs/$filename");
            $arr[] = $filename . '.log';
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
