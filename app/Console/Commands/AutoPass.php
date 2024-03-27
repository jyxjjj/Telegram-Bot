<?php

namespace App\Console\Commands;

use App\Common\Conversation;
use App\Jobs\EditMessageReplyMarkupJob;
use App\Jobs\PassPendingJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class AutoPass extends Command
{
    use DispatchesJobs;

    protected $signature = 'command:autopass';
    protected $description = 'Auto Pass Contributions';

    public function handle(): int
    {
        $pendings = $this->getPending();
        foreach ($pendings as $cvid => $pending) {
            dump("Passing $cvid");
            $messageId = $pending['link'];
            sleep(5);
            $this->dispatch(new PassPendingJob($cvid));
            sleep(5);
            $this->editReplyMarkup($messageId, $cvid);
            sleep(5);
        }
        sleep(5);
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
