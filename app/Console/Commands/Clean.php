<?php

namespace App\Console\Commands;

use App\Common\Conversation;
use Illuminate\Console\Command;

class Clean extends Command
{
    protected $signature = 'command:cleanAll';
    protected $description = 'Clean All Unused Data';

    public function handle(): int
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            $userData = Conversation::get($user, 'contribute');
            unset($userData['status']);
            unset($userData['cvid']);
            foreach ($userData as $cvid => $cinfo) {
                if (!isset($cinfo['status'])) {
                    unset($userData[$cvid]);
                    continue;
                }
                if ($cinfo['status'] != 'pass') {
                    unset($userData[$cvid]);
                }
            }
            if ($userData === []) {
                $this->info("Cleanning $user...");
                unlink(storage_path("app/telegram/conversation/contribute/$user.json"));
            } else {
                $userData = array_merge(['status' => 'free'], $userData);
                Conversation::save($user, 'contribute', $userData);
            }
        }
        unset($user, $userData, $cvid, $cinfo);
        foreach ($users as $user) {
            $userData = Conversation::get($user, 'contribute');
            unset($userData['status']);
            unset($userData['cvid']);
            foreach ($userData as $cvid => $cinfo) {
                $ts = substr($cvid, 0, 10);
                if (strtotime($ts) < strtotime('-1 month')) {
                    unset($userData[$cvid]);
                }
            }
            if ($userData === [] || $userData === ['status' => 'free',]) {
                $this->info("Cleanning $user...");
//                unlink(storage_path("app/telegram/conversation/contribute/$user.json"));
            } else {
                $this->info("Saving $user...");
                $userData = array_merge(['status' => 'free'], $userData);
//                Conversation::save($user, 'contribute', $userData);
            }
        }
        return self::SUCCESS;
    }

    private function getUsers(): array
    {
        $files = glob(storage_path('app/telegram/conversation/contribute/*.json'));
        foreach ($files as &$file) {
            $file = str_replace(storage_path('app/telegram/conversation/contribute/'), '', $file);
            $file = str_replace('.json', '', $file);
        }
        return $files;
    }
}
