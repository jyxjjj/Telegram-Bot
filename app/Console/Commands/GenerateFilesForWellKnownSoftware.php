<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
 *
 * Released under GNU General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Common\ERR;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
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
            foreach (Software::cases() as $software) {
                $templateFile = file_get_contents(__DIR__ . '/../Schedule/WellKnownSoftwareUpdateSubscribe/Softwares/Software.stub');
                if (!is_file($software->file())) {
                    $templateFile = str_replace("{{CLASS}}", $software->name, $templateFile);
                    file_put_contents($software->file(), $templateFile);
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            ERR::log($e);
            return self::FAILURE;
        }
    }
}
