<?php

namespace App\Console\Commands;

use App\Services\Commands\SpeedTestCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;

class Test extends Command
{
    protected $signature = 'command:test';
    protected $description = 'Test command';

    /**
     * @return int
     * @throws BindingResolutionException
     */
    public function handle(): int
    {
        app()->make(SpeedTestCommand::class)->test();
        return self::SUCCESS;
    }
}
