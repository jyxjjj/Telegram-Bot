<?php

namespace App\Services\Commands;

use App\Common\Log\BL;
use App\Common\Log\WL;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class WLBLCommand extends BaseCommand
{
    public string $name = 'wlbl';
    public string $description = 'Set Blacklist and Whitelist';
    public string $usage = '/wlbl {白名单|黑名单|whitelist|blacklist|white|black|wl|bl|w|b} {add|remove|a|r} {用户ID}';
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
        $param = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'text' => '',
        ];
        $param = explode(' ', $param);
        if (count($param) != 3) {
            $data['text'] = '参数数量错误';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $type = $param[0];
        $addOrRemove = $param[1];
        $userId = $param[2];
        if (!is_numeric($userId)) {
            $data['text'] .= '用户ID输入错误';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data['text'] = match ($type) {
            '白名单', 'whitelist', 'white', 'wl', 'w' => match ($addOrRemove) {
                'add', 'a' => WL::add($userId) ? '白名单添加成功' : '白名单添加失败',
                'remove', 'r' => WL::remove($userId) ? '白名单删除成功' : '白名单删除失败',
                default => '添加或删除选项输入错误',
            },
            '黑名单', 'blacklist', 'black', 'bl', 'b' => match ($addOrRemove) {
                'add', 'a' => BL::add($userId) ? '黑名单添加成功' : '黑名单添加失败',
                'remove', 'r' => BL::remove($userId) ? '黑名单删除成功' : '黑名单删除失败',
                default => '添加或删除选项输入错误',
            },
            default => '黑白名单类型输入错误',
        };
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
