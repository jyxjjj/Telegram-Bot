<?php

namespace App\Services\Commands;

use App\Common\BotCommon;
use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class SetAdCommand extends BaseCommand
{
    public string $name = 'setad';
    public string $description = 'Set Ad';
    public string $usage = '/setad';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     * @throws TelegramException
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $text = $message->getText(true);
        if (strlen($text) < 1) {
            $data = [
                'chat_id' => $message->getChat()->getId(),
                'text' => "Usage:\n/setad [position{1,2,3}]\n[ad]",
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data = explode("\n", $text, 2);
        $pos = (int)($data[0] ?? 0);
        $ad = $data[1] ?? '';
        $ad = str_replace('<br/>', "\n", $ad);
        $chatId = $message->getChat()->getId();
        if ($chatId != env('YPP_SOURCE_ID')) {
            $data = [
                'chat_id' => $chatId,
                'text' => 'Permission denied, you are now not in the source group',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $notAdmin = !BotCommon::isAdmin($message);
        if ($notAdmin) {
            $data = [
                'chat_id' => $chatId,
                'text' => 'Permission denied, you are not admin',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        if (!in_array($pos, [1, 2, 3])) {
            $data = [
                'chat_id' => $chatId,
                'text' => 'Position must be 1, 2 or 3',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        if (strlen($ad) < 1) {
            $data = [
                'chat_id' => $chatId,
                'text' => 'Ad cannot be empty',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $originalAd = Conversation::get('ad', 'ad');
        $newAd = $originalAd;
        $newAd[$pos] = $ad;
        Conversation::save('ad', 'ad', $newAd);
        $data = [
            'chat_id' => $chatId,
            'text' => 'Ad set successfully',
        ];
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
