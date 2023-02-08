<?php

namespace App\Services\Commands;

use App\Common\AllowedChats;
use App\Jobs\SendMessageJob;
use App\Models\TChatHistoryOfBindChannel;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class SearchCommand extends BaseCommand
{
    public string $name = 'search';
    public string $description = 'search chat history';
    public string $usage = '/search';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $groups = AllowedChats::getGroups();
        if (!in_array($chatId, $groups)) {
            return;
        }
        Log::debug('SearchCommand: in groups');
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $channelId = AllowedChats::groupGetChannel($chatId);
        $param = $message->getText(true);
        $param = trim($param);
        $param = explode(' ', $param);
        if (count($param) != 1 && count($param) != 2) {
            $data['text'] .= "请输入搜索关键字\n";
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $keyword = $param[0];
        $keyword = str_replace([
            '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+',
            '[', ']', '{', '}', '|', '\\',
            ':', ';', '"', '\'',
            '<', '>', ',', '.', '?', '/',
            '~', '`',
        ], '', $keyword);
        if (strlen($keyword) < 2 || strlen($keyword) > 16) {
            $data['text'] .= "搜索关键字长度必须大于等于2且小于等于16\n";
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $message_ids = TChatHistoryOfBindChannel::searchMessage($channelId, $keyword);
        $message_nums = count($message_ids);
        if ($message_nums < 1) {
            $data['text'] .= "没有找到相关消息\n";
            $this->dispatch(new SendMessageJob($data, null, 0));
        } else if ($message_nums > 10) {
            $page = (int)($param[1] ?? 1);
            if ($page < 1) {
                $data['text'] .= "页码必须大于等于 1 ，已自动设置为 1 \n";
                $page = 1;
            }
            $data['text'] .= "搜索到 $message_nums 条相关消息。\n";
            $max_page = ceil($message_nums / 10);
            $data['text'] .= "总页数： $max_page\n";
            if ($page > $max_page) {
                $data['text'] .= "页码必须小于等于 $max_page ，已自动设置为 $max_page \n";
                $page = $max_page;
            }
            $data['text'] .= "当前页： $page\n";
            $message_ids = array_slice($message_ids, ($page - 1) * 10, 10);
            if (count($message_ids) < 1) {
                $data['text'] .= "没有找到相关消息\n";
                $this->dispatch(new SendMessageJob($data, null, 0));
                return;
            }
            foreach ($message_ids as $message_id) {
                $channelIdLink = substr($channelId, 4);
                $data['text'] .= "https://t.me/c/$channelIdLink/$message_id\n";
            }
            $data['text'] .= "输入 <code>/search $keyword {页码}</code> 翻页\n";
            $data['text'] .= "如 <code>/search $keyword 2</code> 查看第二页\n";
            $this->dispatch(new SendMessageJob($data, null, 0));
        } else {
            $data['text'] .= "搜索到 $message_nums 条相关消息。\n";
            foreach ($message_ids as $message_id) {
                $channelIdLink = substr($channelId, 4);
                $data['text'] .= "https://t.me/c/$channelIdLink/$message_id\n";
            }
            $this->dispatch(new SendMessageJob($data, null, 0));
        }
    }
}
