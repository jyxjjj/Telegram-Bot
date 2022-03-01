<?php

namespace App\Http\Services\Bots\Jobs;

use Longman\TelegramBot\Request;

class BanChatMemberJob extends TelegramBaseQueue
{
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    public function handle()
    {
        Request::banChatMember($this->data);
    }
}
