<?php

namespace App\Services\Commands;

use App\Common\Config;
use App\Common\ERR;
use App\Jobs\SendMessageJob;
use App\Jobs\SendPhotoJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class GetBilibiliCommand extends BaseCommand
{
    public string $name = 'getbilibili';
    public string $description = 'Get Bilibili Video Info';
    public string $usage = '/getbilibili {AVID|BVID}';

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
        $param = $message->getText(true);
        if (!$param) {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => 'Invalid AVID/BVID.',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        try {
            $video = $this->getVideo($param);
        } catch (Throwable $e) {
            ERR::log($e);
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => 'Get Video Info Failed.',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'photo' => $video['photo'],
            'caption' => $video['caption'],
            'reply_markup' => $video['reply_markup'],
        ];
        $this->dispatch(new SendPhotoJob($data, 0));
    }

    private function getVideo(string $vid): array
    {
        $video = $this->getData($vid);
        $video = $video['data'];
        $data['BVID'] = $video['bvid'];
        $data['AVID'] = 'av' . $video['aid'];
        $data['CID'] = $video['cid'];
        $data['title'] = $video['title'];
        $data['cover'] = $video['pic'];
        $data['author'] = $video['owner']['name'];
        $data['thumb_up'] = $video['stat']['like'];
        $data['coins'] = $video['stat']['coin'];
        $data['collect'] = $video['stat']['favorite'];
        $data['play'] = $video['stat']['view'];
        $data['share'] = $video['stat']['share'];
        $data['comment'] = $video['stat']['reply'];
        $data['danmu'] = $video['stat']['danmaku'];
        $data['created'] = Carbon::createFromTimestamp($video['pubdate'])->format('Y-m-d H:i:s');
        $message['photo'] = $data['cover'];
        $message['caption'] = "视频: <b>{$data['title']}</b>\n";
        $message['caption'] .= "UP主: <code>{$data['author']}</code>\n";
        $message['caption'] .= "发布时间: <code>{$data['created']}</code>\n";
        $message['caption'] .= "AV No.: <code>{$data['AVID']}</code>\n";
        $message['caption'] .= "AV Link: <code>https://b23.tv/{$data['AVID']}</code>\n";
        $message['caption'] .= "BV ID: <code>{$data['BVID']}</code>\n";
        $message['caption'] .= "BV Link: <code>https://b23.tv/{$data['BVID']}</code>\n";
        $message['caption'] .= "CID: <code>{$data['CID']}</code>\n";
        $message['caption'] .= "点赞、投币、收藏: {$data['thumb_up']}, {$data['coins']}, {$data['collect']}\n";
        $message['caption'] .= "播放、分享: {$data['play']}, {$data['share']}\n";
        $message['caption'] .= "评论、弹幕: {$data['comment']}, {$data['danmu']}\n";
        $message['reply_markup'] = new InlineKeyboard([]);
        $avButton = new InlineKeyboardButton([
            'text' => "{$data['AVID']}",
            'url' => "https://b23.tv/{$data['AVID']}",
        ]);
        $bvButton = new InlineKeyboardButton([
            'text' => $data['BVID'],
            'url' => "https://b23.tv/{$data['BVID']}",
        ]);
        $message['reply_markup']->addRow($avButton, $bvButton);
        return $message;
    }

    private function getData(string $vid): array
    {
        $vid = substr($vid, 2);
        $link = is_numeric($vid) ? "https://api.bilibili.com/x/web-interface/view?aid=$vid" : "https://api.bilibili.com/x/web-interface/view?bvid=$vid";
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-B23-Spider/$this->version";
        return Http::
        withHeaders($headers)
            ->get($link)
            ->json();
    }
}