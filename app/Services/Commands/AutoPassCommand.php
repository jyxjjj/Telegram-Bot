<?php

namespace App\Services\Commands;

use App\Jobs\AutoPassJob;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class AutoPassCommand extends BaseCommand
{
    public string $name = 'autopass';
    public string $description = 'Autopass Pending Contributions';
    public string $usage = '/autopass';
    public bool $private = false;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        if ($chatId != env('YPP_SOURCE_ID')) {
            return;
        }
        if (Cache::has('autopass')) {
            $data = [
                'chat_id' => $chatId,
                'text' => '[ERROR]自动通过正在处理中，请耐心等待，每10秒只处理1条，处理完毕将发送回复通知，请不要重复发送指令',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data = [
            'chat_id' => $chatId,
            'text' => '[PENDING]开始处理自动通过，请耐心等待，每10秒只处理1条，处理完毕将发送回复通知，请不要重复发送指令',
        ];
        $this->dispatch(new AutoPassJob);
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
