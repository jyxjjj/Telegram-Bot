<?php

namespace App\Jobs;

use App\Jobs\Base\BaseQueue;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DeletePixivFileJob extends BaseQueue
{
    private string $path;

    public function __construct(string $path)
    {
        parent::__construct();
        $this->path = $path;
        $this->delay(180);
    }

    public function handle(): void
    {
        try {
            Storage::disk('public')->delete($this->path);
        } catch (Throwable) {
        }
    }
}
