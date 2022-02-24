<?php

namespace App\Http\Services\Bots\Commands;

use App\Http\Services\Bots\Jobs\SendMessageJob;
use App\Http\Services\Bots\Plugins\DESMG;
use App\Jobs\BaseQueue;

class AboutCommandHandler extends BaseQueue
{
    private int $chatId;

    public function __construct(int $chatId)
    {
        parent::__construct();
        $this->chatId = $chatId;
    }

    public function handle()
    {
        $data = [];
        DESMG::about($data, $this->chatId);
        SendMessageJob::dispatch($data, null, 0);
    }
}
