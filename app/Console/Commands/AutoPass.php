<?php

namespace App\Console\Commands;

use App\Common\Conversation;
use App\Exceptions\Handler;
use App\Jobs\EditMessageReplyMarkupJob;
use App\Jobs\PassPendingJob;
use App\Jobs\SendMessageJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Throwable;

class AutoPass extends Command
{
    use DispatchesJobs;

    protected $signature = 'command:autopass';
    protected $description = 'Auto Pass Contributions';

    public function handle(): int
    {
        if (Cache::has('autopass')) {
            dump('Another instance has already started.');
            return self::FAILURE;
        }
        Cache::set('autopass', 1, 3600);
        try {
            $pendings = $this->getPending();
            foreach ($pendings as $cvid => $pending) {
                Cache::set('autopass', $cvid, 3600);
                dump("Passing $cvid");
                $messageId = $pending['link'];
                $this->dispatch(new PassPendingJob($cvid));
                sleep(5);
                $this->editReplyMarkup($messageId, $cvid);
                sleep(5);
            }
            $data = [
                'chat_id' => env('YPP_SOURCE_ID'),
                'text' => "[SUCCESS]\n全部自动通过处理完成",
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
        } catch (Throwable $e) {
            Handler::logError($e, __FILE__, __LINE__);
        }
        Cache::delete('autopass');
        dump('All Done.');
        return self::SUCCESS;
    }

    private function getPending(): array
    {
        $data = [];
        $pendings = Conversation::get('pending', 'pending');
        $messagelinks = Conversation::get('messagelink', 'pending');
        foreach ($pendings as $cvid => $userId) {
            $data[$cvid] = [
                'user' => $userId,
                'link' => $messagelinks[$cvid],
            ];
        }
        return $data;
    }

    private function editReplyMarkup($messageId, $cvid): void
    {
        $replyMarkupKeyboard = new InlineKeyboard([]);
        $replyMarkupKeyboard->addRow(
            new InlineKeyboardButton([
                'text' => "✅ 已通过(超时) by 🤖 自动",
                'callback_data' => "endedhandle$cvid",
            ]),
        );
        $replyMarkupKeyboardMessage = [
            'chat_id' => env('YPP_SOURCE_ID'),
            'message_id' => $messageId,
            'reply_markup' => $replyMarkupKeyboard,
        ];
        $this->dispatch(new EditMessageReplyMarkupJob($replyMarkupKeyboardMessage));
    }
}
