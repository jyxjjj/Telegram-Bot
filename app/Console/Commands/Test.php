<?php

namespace App\Console\Commands;

use App\Common\B23;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'test';
    protected $description = 'Test';

    public function handle(): int
    {
        dump(
            'TEST',
            B23::BV2AV('BV17x411w7KC'),
            B23::BV2AV('BV17x411w7KC') == 'av170001',
            B23::AV2BV('av170001'),
            B23::AV2BV('av170001') == 'BV17x411w7KC'
        );
        return self::SUCCESS;
    }
}
