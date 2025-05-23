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

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class QueueInstall extends Command
{
    const string SERVICE = <<<EOF
[Unit]
Description={{ APP_NAME }} Horizon Queue Worker
Wants=network-online.target
After=network-online.target

[Service]
User=www
Group=www
WorkingDirectory={{ BASE_DIR }}
ExecStart={{ PHP_BINARY }} -c {{ PHP_INI }} {{ BASE_DIR }}/artisan horizon
Restart=always
RestartSec=1
ExecStop={{ PHP_BINARY }} -c {{ PHP_INI }} {{ BASE_DIR }}/artisan horizon:terminate
KillMode=none
TimeoutStopSec=3600
StandardOutput=null
StandardError=null
PrivateTmp=true
ProtectSystem=full
PrivateDevices=true
ProtectKernelModules=true
ProtectKernelTunables=true
ProtectControlGroups=true
RestrictRealtime=true
RestrictAddressFamilies=AF_INET AF_INET6 AF_NETLINK AF_UNIX
RestrictNamespaces=true

[Install]
WantedBy=multi-user.target

EOF;
    protected $signature = 'queue:install';
    protected $description = 'Install queue service.';

    public function handle(): int
    {
        $root = posix_setgid(0) && posix_setuid(0) && posix_setegid(0) && posix_seteuid(0);
        if (!$root) {
            $this->components->error('Failed to switch to root user.');
            return self::FAILURE;
        }
        $this->components->info('Installing queue service...');
        $result = $this->installService();
        if ($result === self::SUCCESS) {
            $this->components->success('Queue service installed.');
            return self::SUCCESS;
        }
        $this->components->error('Failed to install queue service.');
        return self::FAILURE;
    }

    private function installService(): int
    {
        $app_name = config('app.name');
        $file = "/etc/systemd/system/$app_name-horizon.service";
        if (file_exists($file)) {
            $this->components->error('Service file already exists.');
            return self::FAILURE;
        }
        if (!is_writable('/etc/systemd/system/')) {
            $this->components->error('Service file is not writable.');
            return self::FAILURE;
        }
        $service = str_replace('{{ APP_NAME }}', $app_name, self::SERVICE);
        $service = str_replace('{{ BASE_DIR }}', base_path(), $service);
        $service = str_replace('{{ PHP_BINARY }}', PHP_BINARY, $service);
        $service = str_replace('{{ PHP_INI }}', php_ini_loaded_file(), $service);
        $result = file_put_contents($file, $service);
        if ($result === false) {
            $this->components->error('Failed to write service file.');
            return self::FAILURE;
        }
        $result = Process::run(['systemctl', 'daemon-reload']);
        if ($result->successful()) {
            $this->components->info('Daemon reloaded.');
        } else {
            $this->components->error('Failed to reload daemon.');
            return self::FAILURE;
        }
        $result = Process::run(['systemctl', 'disable', '--now', "$app_name-horizon.service"]);
        if ($result->successful()) {
            $this->components->info("Disabled queue worker.");
        } else {
            $this->components->error("Failed to disable queue worker.");
            return self::FAILURE;
        }
        $result = Process::run(['systemctl', 'enable', '--now', "$app_name-horizon.service"]);
        if ($result->successful()) {
            $this->components->info("Enabled queue worker.");
        } else {
            $this->components->error("Failed to enable queue worker.");
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
