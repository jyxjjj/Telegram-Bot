<?php

namespace App\Services\Commands;

use App\Common\Conversation;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
     * @param Message  $message
     * @param Telegram $telegram
     * @param int      $updateId
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
        $data['text'] .= Conversation::get('ad', 'ad')[2] ?? '';
        $linkOK = true;
        if (str_starts_with($payload, 'get')) {
            $rest = $this->rateLimit($chatId, $username);
            if ($rest == -1) {
                return;
            }
            $data['text'] .= "\n👇👇👇您所获取的链接👇👇👇";
            $cvid = substr($payload, 3);
            $linkData = Conversation::get('link', 'link');
            $link = $linkData[$cvid] ?? "获取链接失败，此链接不存在或已被删除\n您可以访问备份网站或联系管理员咨询\n如果您是链接发布者，可以尝试重新投稿。";
            $linkOK = isset($linkData[$cvid]);
            $data['text'] .= "\n$link\n";
            Log::alert('获取链接', ['username' => $username, 'chatId' => $chatId, 'cvid' => $cvid, 'link' => $link]);
            $data['text'] .= "您今日剩余获取链接次数：$rest\n";
        }
        $data['text'] .= "\nDMCA及版权反馈、技术支持\n";
        $data['text'] .= "请向本机器人发送 /help 命令\n";
        $data['text'] .= "发送 /donate 获取💰捐💰赠💰信息\n";
        $data['text'] .= Conversation::get('ad', 'ad')[3] ?? '';
        //#region reply_markup
        $data['reply_markup'] = new Keyboard([]);
        $data['reply_markup']->setResizeKeyboard(true);
        $data['reply_markup']->addRow(new KeyboardButton('一步投稿'), new KeyboardButton('分步投稿'));
        $data['reply_markup']->addRow(new KeyboardButton('帮助与反馈'), new KeyboardButton('捐赠信息'));
        //#endregion reply_markup
        $this->dispatch(new SendMessageJob($data, null, 0));
        if (!$linkOK) {
            $data = [
                'chat_id' => $chatId,
                'text' => '',
            ];
            $data['text'] .= "尊敬的用户：\n我们近期对数据库进行了整理，在删除旧数据时可能导致新数据被误删除\n如果您获取的链接无效，请向链接发布者反馈，无需咨询管理员\n如果您是链接发布者，您可以重新投稿，确认无效的情况下支持重复投稿，请在描述或标题加【补档】\n目前我们仅保留一个月数据。";
            $this->dispatch(new SendMessageJob($data, null, 0));
        }
    }

    /**
     * @param int         $chatId
     * @param string|null $username
     * @return int
     */
    private function rateLimit(int $chatId, ?string $username): int
    {
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        if ($this->getBlackList($chatId)) {
            $data['text'] .= '您已被拉黑，请联系技术支持 @jyxjjj 或客服 @zaihua_bot';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return -1;
        }
        $times = $this->getGettedTimes($chatId);
        if ($times >= 50) {
            $data['text'] = "您今日获取链接次数已达上限\n";
            $data['text'] .= "发送 /donate 获取💰捐💰赠💰信息";
            $this->dispatch(new SendMessageJob($data, null, 0));
            return -1;
        }
        if ($times > 40) {
            Log::alert("用户 $username ($chatId) 获取链接次数 $times 次");
        }
        $this->addGettedTimes($chatId, $times);
        return 50 - $times;
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
     * @param int      $chatId
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
    private function getBlackList(int $chatId): bool
    {
        return in_array(
            $chatId,
            [
                1553366382, // 此人贩子，不予解封
                1897707227,
                2108116536,
                2143911168,
                366181048,
//                5151499530, 核验后已解封，允许二次封禁。
                5401822276,
                697867344,
                983182500,
            ]
        );
    }
}
