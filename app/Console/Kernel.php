<?php

namespace App\Console;

use App\Console\Schedule\BilibiliSubscribe;
use App\Console\Schedule\ChromeUpdateSubscribe;
use App\Console\Schedule\LogClean;
use App\Console\Schedule\PhpUpdateSubscribe;
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
        $schedule->command(PhpUpdateSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
//        $schedule->command('subscribe:nginx')->hourly()->runInBackground()->withoutOverlapping(120);
//        $schedule->command('subscribe:mariadb')->hourly()->runInBackground()->withoutOverlapping(120);
//        $schedule->command('subscribe:redis')->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(BilibiliSubscribe::class)->everyTenMinutes()->runInBackground()->withoutOverlapping(120);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Schedule');
        $this->load(__DIR__ . '/Commands');
    }
}
