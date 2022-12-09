<?php

namespace App\Console\Commands;

use App\Common\Conversation;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class MigrateLinks extends Command
{
    protected $signature = 'migrate:link';
    protected $description = 'Migrate Link';

    public function handle(): int
    {
        $path = storage_path('app/telegram/conversation/link');
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/^.+\.json$/i'
        );
        foreach ($files as $file) {
            $filename = $file->getFilename();
            if ($filename == 'link.json') {
                continue;
            }
            $cvid = str_replace('.json', '', $filename);
            $linkInfo = Conversation::get($cvid, 'link');
            $newLinkInfo = Conversation::get('link', 'link');
            $newLinkInfo[$cvid] = $linkInfo['link'];
            Conversation::save('link', 'link', $newLinkInfo);
            $pathname = $file->getPathname();
            unlink($pathname);
        }
        return self::SUCCESS;
    }
}
