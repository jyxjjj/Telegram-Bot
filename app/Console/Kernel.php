<?php

namespace App\Console;

use App\Console\Schedule\BilibiliSubscribe;
use App\Console\Schedule\ChromeUpdateSubscribe;
use App\Console\Schedule\LogClean;
use App\Console\Schedule\PixivDownloader;
use App\Console\Schedule\PrivateServerStatusPusher;
use App\Console\Schedule\TRC20Monitor;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Queue\Console\PruneFailedJobsCommand;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Schedule');
        $this->load(__DIR__ . '/Commands');
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(PruneFailedJobsCommand::class, ['--hours=72'])->dailyAt('00:00')->runInBackground()->withoutOverlapping(120);
        $schedule->command(LogClean::class, ['3'])->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(BilibiliSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(ChromeUpdateSubscribe::class)->dailyAt('06:00')->runInBackground()->withoutOverlapping(120);
        $schedule->command(WellKnownSoftwareUpdateSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(PixivDownloader::class)->twiceDaily()->runInBackground()->withoutOverlapping(120);
        $schedule->command(PrivateServerStatusPusher::class)->everyMinute()->runInBackground()->withoutOverlapping(5);
        $schedule->command(TRC20Monitor::class)->everyMinute()->runInBackground()->withoutOverlapping(5);
    }
}
