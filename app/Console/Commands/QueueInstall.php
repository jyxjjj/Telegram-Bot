<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class QueueInstall extends Command
{
    const int NUMPROCS = 4;
    const string SERVICE = <<<EOF
[Unit]
Description={{ APP_NAME }} Queue Worker %i
Wants=network-online.target
After=network-online.target

[Service]
User=www
Group=www
WorkingDirectory={{ BASE_DIR }}
ExecStart={{ PHP_BINARY }} -c {{ PHP_INI }} {{ BASE_DIR }}/artisan queue:work --sleep=0.05 --max-time=3600 --timeout=1800
ExecReload={{ PHP_BINARY }} -c {{ PHP_INI }} {{ BASE_DIR }}/artisan queue:restart
Restart=always
RestartSec=1
KillMode=control-group
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
            $this->components->info('Queue service installed.');
            return self::SUCCESS;
        }
        $this->components->error('Failed to install queue service.');
        return self::FAILURE;
    }

    private function installService(): int
    {
        $app_name = config('app.name');
        $file = "/etc/systemd/system/$app_name-queue@.service";
        if (file_exists($file)) {
            $this->components->error('Service file already exists.');
            return self::FAILURE;
        }
        if (!is_writable('/etc/systemd/system')) {
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
        $numprocs = self::NUMPROCS;
        foreach (range(1, $numprocs) as $i) {
            $result = Process::run(['systemctl', 'disable', '--now', "$app_name-queue@$i.service"]);
            if ($result) {
                $this->components->info("Disabled queue worker $i.");
            } else {
                $this->components->error("Failed to disable queue worker $i.");
                return self::FAILURE;
            }
        }
        foreach (range(1, $numprocs) as $i) {
            $result = Process::run(['systemctl', 'enable', '--now', "$app_name-queue@$i.service"]);
            if ($result) {
                $this->components->info("Enabled queue worker $i.");
            } else {
                $this->components->error("Failed to enable queue worker $i.");
                return self::FAILURE;
            }
        }
        return self::SUCCESS;
    }
}