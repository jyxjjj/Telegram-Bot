<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:prune-failed --hours 72')->dailyAt('00:00')->runInBackground()->withoutOverlapping(120);
        $schedule->command('log:clean 3')->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command('subscribe:bilibili')->everyFiveMinutes()->runInBackground()->withoutOverlapping(120);
        $schedule->command('subscribe:chrome')->hourly()->runInBackground()->withoutOverlapping(120);
//        $schedule->command('subscribe:php')->hourly()->runInBackground()->withoutOverlapping(120);
//        $schedule->command('subscribe:nginx')->hourly()->runInBackground()->withoutOverlapping(120);
//        $schedule->command('subscribe:mariadb')->hourly()->runInBackground()->withoutOverlapping(120);
//        $schedule->command('subscribe:redis')->hourly()->runInBackground()->withoutOverlapping(120);
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Schedule');
        $this->load(__DIR__ . '/Commands');
    }
}
