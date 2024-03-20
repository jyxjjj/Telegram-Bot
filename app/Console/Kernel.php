<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Queue\Console\PruneFailedJobsCommand;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(PruneFailedJobsCommand::class, ['--hours=72'])->dailyAt('00:00')->runInBackground()->withoutOverlapping(120);
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
