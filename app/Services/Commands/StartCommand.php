<?php

namespace App\Services\Commands;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class StartCommand extends BaseCommand
{
    public string $name = 'start';
    public string $description = 'Start command';
    public string $usage = '/start';
    public bool $private = true;

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        if (!$message->getChat()->isPrivateChat()) {
            return;
        }
        $chatId = $message->getChat()->getId();
        $username = $message->getChat()->getUsername();
        $payload = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $data['text'] .= env('LONG_START_AD');
        if (str_starts_with($payload, 'get')) {
            $rest = $this->rateLimit($chatId, $username);
            if ($rest == -1) {
                return;
            }
            $data['text'] .= "\n👇👇👇您所获取的链接👇👇👇";
            $cvid = substr($payload, 3);
            $linkData = Conversation::get('link', 'link');
            $link = $linkData[$cvid] ?? "获取链接失败\n请联系管理员";
            $data['text'] .= "\n$link\n";
            $data['text'] .= "您今日剩余获取链接次数：$rest\n";
        }
        $data['text'] .= "\nDMCA及版权反馈、技术支持\n";
        $data['text'] .= "请向本机器人发送 /help 命令\n";
        //#region reply_markup
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $button1 = new KeyboardButton('阿里云盘分步投稿');
        $button2 = new KeyboardButton('阿里云盘一步投稿');
        $data['reply_markup']->addRow($button1);
        $data['reply_markup']->addRow($button2);
        //#endregion reply_markup
        $this->dispatch(new SendMessageJob($data, null, 0));
    }

    /**
     * @param int $chatId
     * @param string $username
     * @return int
     */
    private function rateLimit(int $chatId, ?string $username): int
    {
        if ($this->getBlackList($chatId)) {
            $data = [
                'chat_id' => $chatId,
                'text' => '您已被拉黑，请联系技术支持 @jyxjjj 或客服 @zaihua_bot',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return -1;
        }
        $times = $this->getGettedTimes($chatId);
        if ($times >= 30) {
            $data = [
                'chat_id' => $chatId,
                'text' => '您今日已达到上限',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return -1;
        }
        $this->addGettedTimes($chatId, $times);
        is_null($username) && $username = '无用户名';
        if (Cache::get($chatId . '_start') >= 20) {
            $data = [
                'chat_id' => env('YPP_SOURCE_ID'),
                'text' => "<a href='tg://user?id=$chatId'>$chatId</a> $username 获取链接次数达到 $times 次",
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
        }
        return 30 - $times;
    }

    /**
     * @param int $chatId
     * @return int
     */
    private function getGettedTimes(int $chatId): int
    {
        return (int)Cache::get($chatId . '_start', 0);
    }

    /**
     * @param int $chatId
     * @param int|null $times
     * @return void
     */
    private function addGettedTimes(int $chatId, int &$times = null): void
    {
        if ($times == null) {
            $times = $this->getGettedTimes($chatId);
        }
        $times++;
        Cache::put($chatId . '_start', $times, Carbon::tomorrow()->diffInSeconds());
    }

    /**
     * @param int $chatId
     * @return bool
     */
    protected function getBlackList(int $chatId): bool
    {
        return in_array($chatId, [366181048, 697867344, 983182500, 1897707227, 2108116536]);
    }
}
