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
        dump(B23::BV2AV('BV1Ty421Y7gV') == 'av1550274374', B23::AV2BV('av1550274374') == 'BV1Ty421Y7gV');
        return self::SUCCESS;
    }
}
