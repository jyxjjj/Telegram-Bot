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
    private int $a2bAddEnc = 8728348608;
    private int $a2bXorEnc = 0b1010100100111011001100100100;
    private array $a2bEncIndex = [11, 10, 3, 8, 4, 6];
    private string $a2bEncTable = "fZodR9XQDSUm21yCkr6zBqiveYah8bt4xsWpHnJE7jL5VG3guMTKNPAwcF";

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $param = $message->getText(true);
        if (!$param) {
            $data = [
                'chat_id' => $chatId,
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
                'text' => 'Get Video Info Failed.',
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        }
        $data = [
            'chat_id' => $chatId,
            'photo' => $video['photo'],
            'caption' => $video['caption'],
            'reply_markup' => $video['reply_markup'],
        ];
        $this->dispatch(new SendPhotoJob($data, 0));
    }

    private function getVideo(string $vid): array
    {
        $avid = str_starts_with($vid, 'BV') ? $this->BV2AV($vid) : $vid;
        $video = $this->getData($avid);
        $video = $video['data'];
        $data['BVID'] = $video['bvid'];
        $data['AVID'] = 'av' . $video['aid'];
        $data['CID'] = 'av' . $video['cid'];
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

    private function BV2AV(string $bv): string
    {
        $temp = 0;
        for ($i = 0; $i < count($this->a2bEncIndex); $i++) {
            if (!str_contains($this->a2bEncTable, $bv[$this->a2bEncIndex[$i]])) {
                return 'Invaild BV ID.';
            } else {
                $temp += strpos($this->a2bEncTable, $bv[$this->a2bEncIndex[$i]]) * pow(strlen($this->a2bEncTable), $i);
            }
        }
        $temp = $temp - $this->a2bAddEnc ^ $this->a2bXorEnc;
        return 'av' . $temp;
    }

    private function getData(string $avid)
    {
        $avid = str_replace('av', '', $avid);
        $link = "https://api.bilibili.com/x/web-interface/view?aid=$avid";
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-B23-Spider/$this->version";
        return Http::
        withHeaders($headers)
            ->get($link)
            ->json();
    }
}
