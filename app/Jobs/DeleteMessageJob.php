<?php

namespace App\Jobs;

use App\Common\RequestService;
use App\Jobs\Base\BaseQueue;

class DeleteMessageJob extends BaseQueue
{
    private array $data;

    /**
     * @param array $data
     * @param int $delay
     */
    public function __construct(array $data, int $delay = 60)
    {
        parent::__construct();
        $this->data = $data;
        $this->delay($delay);
    }

    public function handle(): void
    {
        if (!RequestService::getInstance()->deleteMessage($this->data)) {
            $this->release(1);
        }
    }
}
