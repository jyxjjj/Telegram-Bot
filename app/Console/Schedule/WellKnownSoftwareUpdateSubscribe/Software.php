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

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe;

use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\CURL;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\FFmpeg;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\Go;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\Kernel;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\KernelFeodra;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\Laravel;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\MariaDB;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\MariaDBDocker;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\Nginx;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\NodeJS;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\NVM;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\OpenSSL;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\PHP;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\Redis;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\RedisDocker;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares\VSCode;

enum Software: string
{
    case PHP = 'PHP';
    case Nginx = 'NGINX';
    case MariaDB = 'MARIADB';
    case MariaDBDocker = 'MARIADB_DOCKER';
    case Redis = 'REDIS';
    case RedisDocker = 'REDIS_DOCKER';
    case OpenSSL = 'OPENSSL';
    case Go = 'GO';
    case NodeJS = 'NODEJS';
    case Kernel = 'KERNEL';
    case KernelFeodra = 'KERNEL_FEDORA';
    case VSCode = 'VSCODE';
    case Laravel = 'LARAVEL';
    case CURL = 'CURL';
    case FFmpeg = 'FFMPEG';
    case NVM = 'NVM';

    public function file(): string
    {
        return match ($this) {
            self::PHP => __DIR__ . '/Softwares/PHP.php',
            self::Nginx => __DIR__ . '/Softwares/Nginx.php',
            self::MariaDB => __DIR__ . '/Softwares/MariaDB.php',
            self::MariaDBDocker => __DIR__ . '/Softwares/MariaDBDocker.php',
            self::Redis => __DIR__ . '/Softwares/Redis.php',
            self::RedisDocker => __DIR__ . '/Softwares/RedisDocker.php',
            self::OpenSSL => __DIR__ . '/Softwares/OpenSSL.php',
            self::Go => __DIR__ . '/Softwares/Go.php',
            self::NodeJS => __DIR__ . '/Softwares/NodeJS.php',
            self::Kernel => __DIR__ . '/Softwares/Kernel.php',
            self::KernelFeodra => __DIR__ . '/Softwares/KernelFeodra.php',
            self::VSCode => __DIR__ . '/Softwares/VSCode.php',
            self::Laravel => __DIR__ . '/Softwares/Laravel.php',
            self::CURL => __DIR__ . '/Softwares/CURL.php',
            self::FFmpeg => __DIR__ . '/Softwares/FFmpeg.php',
            self::NVM => __DIR__ . '/Softwares/NVM.php',
        };
    }

    public function getInstance(): SoftwareInterface
    {
        if (!isset(Common::$instances[$this->value]) || !Common::$instances[$this->value] instanceof SoftwareInterface) {
            Common::$instances[$this->value] = new ($this->getClass());
        }
        return Common::$instances[$this->value];
    }

    public function getClass(): string
    {
        return match ($this) {
            self::PHP => PHP::class,
            self::Nginx => Nginx::class,
            self::MariaDB => MariaDB::class,
            self::MariaDBDocker => MariaDBDocker::class,
            self::Redis => Redis::class,
            self::RedisDocker => RedisDocker::class,
            self::OpenSSL => OpenSSL::class,
            self::Go => Go::class,
            self::NodeJS => NodeJS::class,
            self::Kernel => Kernel::class,
            self::KernelFeodra => KernelFeodra::class,
            self::VSCode => VSCode::class,
            self::Laravel => Laravel::class,
            self::CURL => CURL::class,
            self::FFmpeg => FFmpeg::class,
            self::NVM => NVM::class,
        };
    }
}
