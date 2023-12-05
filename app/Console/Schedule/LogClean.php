<?php

namespace App\Console\Schedule;

use App\Exceptions\Handler;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
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
            if ($preserve < 3 && !App::isLocal()) {
                self::error('Days to preserve must be greater than 3');
                return self::INVALID;
            }
            $path = storage_path('logs');
            $files = glob($path . '/*.log');
            $whitelist = $this->generateWhiteLists($preserve);
            self::info("Using RegExp: $whitelist");
            foreach ($files as $fileName) {
                $fullFileName = $fileName;
                $fileName = basename($fileName);
                if (!preg_match($whitelist, $fileName)) {
                    self::error("Deleting $fileName...");
                    unlink($fullFileName);
                } else {
                    self::info("Preserved $fileName.");
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            self::error($e->getMessage());
            Handler::logError($e);
            return self::FAILURE;
        }
    }

    private function generateWhiteLists(int $int): string
    {
        $now = Carbon::createFromTimestamp(LARAVEL_START);
        $times = [];
        $times[] = $now->format('Y-m-d');
        for ($i = 0; $i < $int; $i++) {
            $times[] = $now->subDay()->format('Y-m-d');
        }
        $times = implode('|', $times);
        $type = [
            'single',
            'sql',
            'perf',
            'deprecations',
            'emergency',
            'schedule',
        ];
        $type = implode('|', $type);
        /** @lang PhpRegExp */
        return "/^(($times)\.($type)|.+\.queue\.\d+\.(out|err))\.log$/i";
    }
}
