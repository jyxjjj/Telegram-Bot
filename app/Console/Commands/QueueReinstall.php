<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueueReinstall extends Command
{
    protected $signature = 'queue:reinstall';
    protected $description = 'Reinstall the queue service';

    public function handle(): int
    {
        $root = posix_setgid(0) && posix_setuid(0) && posix_setegid(0) && posix_seteuid(0);
        if (!$root) {
            $this->components->error('Failed to switch to root user.');
            return self::FAILURE;
        }
        $file = '/etc/systemd/system/' . config('app.name') . '-queue@.service';
        if (!file_exists($file)) {
            $this->components->warn('Service file not found.');
        } else {
            unlink($file);
        }
        $this->call('queue:install');
        return self::SUCCESS;
    }
}
