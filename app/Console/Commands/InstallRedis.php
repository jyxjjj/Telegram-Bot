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

namespace App\Console\Commands;

use App\Common\RequestHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Throwable;

class InstallRedis extends Command
{
    const string CONTAINER = <<<EOF
[Unit]
Description=Redis Container

[Container]
ContainerName=redis
Image=docker.io/library/redis:7.4.2
Pull=never

Timezone=Etc/GMT-8
Environment=TZ=Etc/GMT-8

Volume=/www/server/redis/conf/:/etc/redis/
Volume=/www/server/redis/data/:/data/

PublishPort=6379:6379

Network=podman
NetworkAlias=redis
HostName=redis
IP=10.88.0.3

HealthCmd=none

StartWithPod=true

StopTimeout=10

DNS=8.8.8.8
DNS=8.8.4.4
DNS=1.1.1.1
DNS=1.0.0.1
DNS=223.5.5.5
DNS=223.6.6.6

Sysctl=net.ipv6.conf.all.disable_ipv6=1
Sysctl=net.ipv6.conf.default.disable_ipv6=1
Sysctl=net.ipv6.conf.lo.disable_ipv6=1
Sysctl=net.ipv4.conf.all.rp_filter=0
Sysctl=net.ipv4.conf.default.rp_filter=0
Sysctl=net.ipv4.conf.default.arp_announce=2
Sysctl=net.ipv4.conf.lo.arp_announce=2
Sysctl=net.ipv4.conf.all.arp_announce=2
Sysctl=net.ipv4.tcp_max_tw_buckets=5000
Sysctl=net.ipv4.tcp_syncookies=1
Sysctl=net.ipv4.tcp_max_syn_backlog=2048
Sysctl=net.core.somaxconn=51200
Sysctl=net.ipv4.tcp_synack_retries=2
Sysctl=net.ipv4.tcp_fastopen=3

Ulimit=nofile=65535:65535

CgroupsMode=enabled

UIDMap=0:0:1
GIDMap=0:0:1
UIDMap=999:1001:1
GIDMap=999:1001:1

Exec=redis-server /etc/redis/redis.conf

[Service]
User=root
Group=root
Restart=always
TimeoutStartSec=15

[Install]
WantedBy=multi-user.target

EOF;

    protected $signature = 'install:redis';
    protected $description = 'Install Redis service.';

    public function handle(): int
    {
        $root = posix_setgid(0) && posix_setuid(0) && posix_setegid(0) && posix_seteuid(0);
        if (!$root) {
            $this->components->error('Failed to switch to root user.');
            return self::FAILURE;
        }
        $this->components->info('Installing Redis service...');
        $result = $this->installService();
        if ($result === self::SUCCESS) {
            $this->components->success('Redis service installed.');
            return self::SUCCESS;
        }
        $this->components->error('Failed to install Redis service.');
        return self::FAILURE;
    }

    private function installService(): int
    {
        $apiSocket = '/var/run/podman/podman.sock';
        if (!file_exists($apiSocket)) {
            $result = Process::run(['systemctl', 'restart', 'podman.socket']);
            if ($result->successful()) {
                $this->components->info('Podman socket restarted.');
            } else {
                $this->components->error('Failed to restart Podman socket.');
                return self::FAILURE;
            }
            if (!file_exists($apiSocket)) {
                $this->components->error('Podman API not available.');
                return self::FAILURE;
            }
        }
        try {
            $result = RequestHelper::getInstance()
                ->withOptions([
                    'force_ip_resolve' => null,
                    'curl' => [
                        CURLOPT_UNIX_SOCKET_PATH => $apiSocket,
                    ],
                ])
                ->get('/libpod/version');
            if ($result->ok()) {
                if ($result->body() === 'Not Found') {
                    $this->components->error('Podman version too low.');
                    return self::FAILURE;
                }
                $version = $result->json()['Version'];
            } else {
                $this->components->error('Podman API returned error: ' . $result->status());
                return self::FAILURE;
            }
        } catch (Throwable $e) {
            $this->components->error('Podman API not available: ' . $e->getMessage());
            return self::FAILURE;
        }
        if (version_compare($version, '5.3.1', '<')) {
            $this->components->error('Podman version ' . $version . ' too low.');
            return self::FAILURE;
        } else {
            $this->components->info('Podman version: ' . $version);
        }
        $file = '/etc/containers/systemd/redis.container';
        if (file_exists($file)) {
            $this->components->error('Quadlet file already exists.');
            return self::FAILURE;
        }
        if (!is_writable('/etc/containers/systemd/')) {
            $this->components->error('Quadlet file is not writable.');
            return self::FAILURE;
        }
        $service = self::CONTAINER;
        $result = file_put_contents($file, $service);
        if ($result === false) {
            $this->components->error('Failed to write Quadlet file.');
            return self::FAILURE;
        }
        $result = Process::run(['systemctl', 'daemon-reload']);
        if ($result->successful()) {
            $this->components->info('Daemon reloaded.');
        } else {
            $this->components->error('Failed to reload daemon.');
            return self::FAILURE;
        }
        $result = Process::run(['systemctl', 'disable', '--now', 'redis.service']);
        if ($result->successful()) {
            $this->components->info('Disabled Redis service.');
        } else {
            $this->components->error('Failed to disable Redis service.');
            return self::FAILURE;
        }
        $result = Process::run(['systemctl', 'enable', '--now', 'redis.service']);
        if ($result->successful()) {
            $this->components->info('Enabled Redis service.');
        } else {
            $this->components->error('Failed to enable Redis service.');
            return self::FAILURE;
        }
        $this->components->warn('You should manually check the configration file on `/www/server/redis/conf/redis.conf`');
        $this->components->warn('If the configuration file is not correct, you should manually modify it.');
        $this->components->warn('After modifying the configuration file, restart the Redis Container via `systemctl restart redis.service`');
        $this->components->warn('Edit .env file and set the Redis connection configurations.');
        return self::SUCCESS;
    }
}
