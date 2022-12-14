<?php

namespace App\Console\Commands;

use App\Common\Conversation;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class FindUserByCVID extends Command
{
    protected $signature = 'command:FindUserByCVID {cvid}';
    protected $description = '';

    /**
     * @return int
     */
    public function handle(): int
    {
        $cvid = self::argument('cvid');
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
            foreach ($userData as $t_cvid => $cvinfo) {
                if ($t_cvid == $cvid) {
                    self::info($userId);
                }
            }
        }
        return self::SUCCESS;
    }
}
