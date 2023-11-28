<?php

namespace App\Console\Commands;

use App\Common\Conversation;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class FixLinks extends Command
{
    protected $signature = 'command:FixLinks';
    protected $description = '';

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
            foreach ($userData as $cvid => $cvinfo) {
                if (str_starts_with($cvid, '16') || str_starts_with($cvid, '17')) {
                    if ($cvinfo['status'] == 'pass') {
                        self::info($cvid);
                        $link = $cvinfo['link'];
                        $newLinks = Conversation::get('link', 'link');
                        $newLinks[$cvid] = $link;
                        Conversation::save('link', 'link', $newLinks);
                    }
                }
            }
        }
        return self::SUCCESS;
    }
}
