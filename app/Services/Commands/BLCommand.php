<?php

namespace App\Services\Commands;

use App\Common\Log\BL;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BLCommand extends BaseCommand
{
    public string $name = 'bl';
    public string $description = 'Set Blacklist';
    public string $usage = '/bl {add|remove|a|r} {用户ID}';
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
        if (count($param) != 2) {
            $data['text'] = '参数数量错误';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $addOrRemove = $param[0];
        $userId = $param[1];
        if (!is_numeric($userId)) {
            $data['text'] .= '用户ID输入错误';
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data['text'] = match ($addOrRemove) {
            'add', 'a' => BL::add($userId) ? '黑名单添加成功' : '黑名单添加失败',
            'remove', 'r' => BL::remove($userId) ? '黑名单删除成功' : '黑名单删除失败',
            default => '添加或删除选项输入错误',
        };
        $this->dispatch(new SendMessageJob($data, null, 0));
    }
}
