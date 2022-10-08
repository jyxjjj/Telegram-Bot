<?php

namespace App\Console\Commands;

use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
use App\Exceptions\Handler;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Throwable;

class GenerateFilesForWellKnownSoftware extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:generate';
    protected $description = 'Generate Software Interface Handlers';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $templateFile = file_get_contents(__DIR__ . '/../Schedule/WellKnownSoftwareUpdateSubscribe/Softwares/Software.stub');
            foreach (Software::cases() as $software) {
                if (!is_file($software->file())) {
                    $templateFile = str_replace("{{CLASS}}", $software->name, $templateFile);
                    file_put_contents($software->file(), $templateFile);
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            Handler::logError($e);
            return self::FAILURE;
        }
    }
}
