<?php

namespace App\Console\Schedule;

use Illuminate\Console\Command;
use Throwable;

class BilibiliSubscribe extends Command
{
    protected $signature = 'bilibili:subscribe';
    protected $description = 'Get Subscribed UPs\' video lists then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {

            return self::SUCCESS;
        } catch (Throwable $e) {
            self::error($e->getMessage());
            return self::FAILURE;
        }
    }
}
