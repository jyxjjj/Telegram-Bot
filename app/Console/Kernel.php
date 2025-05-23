<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console;

use App\Console\Schedule\BilibiliSubscribe;
use App\Console\Schedule\ChromeUpdateSubscribe;
use App\Console\Schedule\JetBrainsUpdateSubscribe;
use App\Console\Schedule\PixivDownloader;
use App\Console\Schedule\PrivateServerStatusPusher;
use App\Console\Schedule\TRC20Monitor;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Queue\Console\PruneFailedJobsCommand;
use Laravel\Horizon\Console\SnapshotCommand;

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
        $schedule->command(SnapshotCommand::class)->everyMinute()->runInBackground()->withoutOverlapping(120);
        $schedule->command(BilibiliSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(ChromeUpdateSubscribe::class)->dailyAt('06:00')->runInBackground()->withoutOverlapping(120);
        $schedule->command(JetBrainsUpdateSubscribe::class)->dailyAt('06:00')->runInBackground()->withoutOverlapping(120);
        $schedule->command(WellKnownSoftwareUpdateSubscribe::class)->hourly()->runInBackground()->withoutOverlapping(120);
        $schedule->command(PixivDownloader::class)->twiceDaily()->runInBackground()->withoutOverlapping(120);
        $schedule->command(PrivateServerStatusPusher::class)->everyMinute()->runInBackground()->withoutOverlapping(5);
        $schedule->command(TRC20Monitor::class)->everyMinute()->runInBackground()->withoutOverlapping(5);
    }
}
