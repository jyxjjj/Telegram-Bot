<?php

namespace App\Console;

use App\Console\Schedule\ChromeUpdateSubscribe;
use App\Console\Schedule\LogClean;
use App\Console\Schedule\PixivDownloader;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Queue\Console\PruneFailedJobsCommand;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command(PruneFailedJobsCommand::class, ['--hours=72'])->dailyAt('00:00')->runInBackground()->withoutOverlapping(120);
        $schedule->command(LogClean::class, ['3'])->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(ChromeUpdateSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(WellKnownSoftwareUpdateSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(PixivDownloader::class)->twiceDaily()->runInBackground()->withoutOverlapping(120);
//        $schedule->command(BilibiliSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Schedule');
        $this->load(__DIR__ . '/Commands');
    }
}
