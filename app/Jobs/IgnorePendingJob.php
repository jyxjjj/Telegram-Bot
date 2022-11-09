<?php

namespace App\Jobs;

use App\Common\Conversation;
use App\Jobs\Base\BaseQueue;

class IgnorePendingJob extends BaseQueue
{
    private string $data;

    public function __construct(string $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    public function handle()
    {
        $cvid = $this->data;
        $pendingData = Conversation::get('pending', 'pending');
        $user_id = $pendingData[$cvid];
        unset($pendingData[$cvid]);
        Conversation::save('pending', 'pending', $pendingData);
        unset($pendingData);
        $userData = Conversation::get($user_id, 'contribute');
        $userData[$cvid]['status'] = 'ignore';
        Conversation::save($user_id, 'contribute', $userData);
    }
}
