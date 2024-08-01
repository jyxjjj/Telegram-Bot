<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
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

namespace App\Console\Schedule;

use App\Common\ERR;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
use App\Jobs\SendMessageJob;
use App\Models\TUpdateSubscribes;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Throwable;

class WellKnownSoftwareUpdateSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:update {--s|software=} {software?}';
    protected $description = 'Get the Newest Version then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            if ($this->option('software') || $this->argument('software')) {
                $software = Software::from($this->option('software') ?? $this->argument('software'));
                $this->dealCheck($software);
            } else {
                foreach (Software::cases() as $software) {
                    self::info("Checking $software->name...");
                    try {
                        $this->dealCheck($software);
                    } catch (Throwable $e) {
                        ERR::log($e);
                        continue;
                    }
                    sleep(1);
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            ERR::log($e);
            return self::FAILURE;
        }
    }

    private function dealCheck(Software $software): void
    {
        /** @var TUpdateSubscribes[] $datas */
        $datas = TUpdateSubscribes::getAllSubscribeBySoftware($software->name);
        foreach ($datas as $data) {
            $chat_id = $data['chat_id'];
            $version = $software->getInstance()->getVersion();
            $lastVersion = Common::getLastVersion($software);
            self::info("$software->name Current:$version Latest:$lastVersion");
            if ($version && $lastVersion != $version) {
                $message = $software->getInstance()->generateMessage($chat_id, $version);
                $this->dispatch(new SendMessageJob($message, null, 0));
                Common::setLastVersion($software, $version);
            }
        }
    }
}
