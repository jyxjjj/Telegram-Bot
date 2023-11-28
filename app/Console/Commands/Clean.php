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
//        foreach ($users as $user) {
//            $userData = Conversation::get($user, 'contribute');
//            unset($userData['status']);
//            unset($userData['cvid']);
//            foreach ($userData as $cvid => $cinfo) {
//                if (!isset($cinfo['status'])) {
//                    unset($userData[$cvid]);
//                    continue;
//                }
//                if ($cinfo['status'] != 'pass') {
//                    unset($userData[$cvid]);
//                }
//            }
//            if ($userData === []) {
//                $this->info("Cleanning1 $user...");
//                unlink(storage_path("app/telegram/conversation/contribute/$user.json"));
//            } else {
//                $this->info("Saving1 $user...");
//                $userData = array_merge(['status' => 'free'], $userData);
//                Conversation::save($user, 'contribute', $userData);
//            }
//        }
//        unset($user, $userData, $cvid, $cinfo);
//        foreach ($users as $user) {
//            $userData = Conversation::get($user, 'contribute');
//            unset($userData['status']);
//            unset($userData['cvid']);
//            foreach ($userData as $cvid => $cinfo) {
//                $ts = substr($cvid, 0, 10);
//                dump($ts, strtotime('-1 month'));
//                if ($ts < strtotime('-1 month')) {
//                    unset($userData[$cvid]);
//                }
//            }
//            if ($userData === [] || $userData === ['status' => 'free',]) {
//                $this->info("Cleanning2 $user...");
//                unlink(storage_path("app/telegram/conversation/contribute/$user.json"));
//            } else {
//                $this->info("Saving2 $user...");
//                $userData = array_merge(['status' => 'free'], $userData);
//                Conversation::save($user, 'contribute', $userData);
//            }
//        }
//        unset($user, $userData, $cvid, $cinfo);
        $savingLinks = [];
        foreach ($users as $user) {
            $userData = Conversation::get($user, 'contribute');
            unset($userData['status']);
            unset($userData['cvid']);
            foreach ($userData as $cvid => $cinfo) {
                $link = $cinfo['link'];
                $link = trim($link);
                $link = str_replace("\n\n", "\n", $link);
                $link = str_replace(' ', '', $link);
                $link = str_replace('📁', '', $link);
                $link = preg_replace('/大小：(.*)/', '', $link);
                $link = trim($link);
                if (filter_var($link, FILTER_VALIDATE_URL) === false) {
                    dd($link);
                }
                $userData[$cvid]['link'] = $link;
                $savingLinks[$cvid] = $link;
            }
            $userData = array_merge(['status' => 'free'], $userData);
            Conversation::save($user, 'contribute', $userData);
        }
        Conversation::save('link', 'link', $savingLinks);
        unset($user, $userData, $cvid, $cinfo, $link, $savingLinks);
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
