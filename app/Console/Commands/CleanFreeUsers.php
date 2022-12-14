<?php

namespace App\Console\Commands;

use App\Common\Conversation;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class CleanFreeUsers extends Command
{
    protected $signature = 'command:CleanFreeUsers';
    protected $description = 'Clean Free Users';

    /**
     * @return int
     */
    public function handle(): int
    {
        $path = storage_path('app/telegram/conversation/contribute');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+\.json$/i'
        );
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $userId = str_replace('.json', '', $filename);
            $userData = Conversation::get($userId, 'contribute');
            if (count($userData) == 0) {
                continue;
            }
            if ($userData === ['status' => 'free']) {
                self::info($filename);
                unlink($file->getPathname());
            }
        }
        return self::SUCCESS;
    }
}
