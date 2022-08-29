<?php

namespace App\Console\Schedule;

use App\Common\Config;
use App\Jobs\SendPhotoJob;
use App\Models\TBilibiliSubscribes;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Throwable;

class BilibiliSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'bilibili:subscribe';
    protected $description = 'Get Subscribed UPs\' video lists then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            self::info("Start to get subscribed UPs' video lists");
            /** @var TBilibiliSubscribes[] $datas */
            $datas = TBilibiliSubscribes::getAllSubscribe();
            foreach ($datas as $data) {
                $chat_id = $data->chat_id;
                $mid = $data->mid;
                self::info("Get subscribed UPs' video lists of $mid");
                $message = [
                    'chat_id' => $chat_id,
                    'caption' => '',
                ];
                $videoList = $this->getVideoList($mid);
                $last_send = $this->getLastSend($chat_id, $mid);
                if (!$last_send) {
                    self::info("Haven't send any video to $chat_id with $mid");
                    $video = $videoList[0];
                } else {
                    for ($i = 0; $i < count($videoList); $i++) {
                        if ($i == 0 && $videoList[$i]['bvid'] == $last_send) {
                            self::info("There is no new video of $mid");
                            continue 2;
                        }
                        if ($videoList[$i]['bvid'] == $last_send) {
                            self::info("Find new video of $mid");
                            $video = $videoList[$i - 1];
                            break;
                        }
                    }
                }
                if (isset($video)) {
                    self::info("New video of $mid is {$video['bvid']}");
                    $message['photo'] = $video['pic'];
                    $message['caption'] .= "Name: *{$video['title']}*\n";
                    $message['caption'] .= "Author: `{$video['author']}`\n";
                    $message['caption'] .= "Created: `{$video['created']}`\n";
                    $message['caption'] .= "AV No.: [{$video['aid']}](https://www.bilibili.com/av{$video['aid']})\n";
                    $message['caption'] .= "BV ID: [{$video['bvid']}](https://www.bilibili.com/{$video['bvid']})\n";
                    $message['caption'] .= "Comments: {$video['comment']}\n";
                    $message['caption'] .= "Viewed Times: {$video['video_review']}\n";
                    $message['reply_markup'] = new InlineKeyboard([]);
                    $avButton = new InlineKeyboardButton([
                        'text' => "AV{$video['aid']}",
                        'url' => "https://www.bilibili.com/av{$video['aid']}",
                    ]);
                    $bvButton = new InlineKeyboardButton([
                        'text' => $video['bvid'],
                        'url' => "https://www.bilibili.com/{$video['bvid']}",
                    ]);
                    $message['reply_markup']->addRow($avButton, $bvButton);
                    self::info("Send new video {$video['bvid']} of $mid to $chat_id");
                    $this->dispatch(new SendPhotoJob($message, 0));
                    $this->setLastSend($chat_id, $mid, $video['bvid']);
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            self::error($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * @param int $mid
     * @return array
     */
    private function getVideoList(int $mid): array
    {
        $data = Cache::get("Schedule::BilibiliSubscribe::mid_info::{$mid}", false);
        if ($data) {
            return $data;
        }
        $url = "https://api.bilibili.com/x/space/arc/search?mid={$mid}&ps=5&order=pubdate";
        $json = $this->getJson($url);
        $vlist = $json['data']['list']['vlist'];
        foreach ($vlist as &$video) {
            $video['created'] = Carbon::createFromTimestamp($video['created'])->format('Y-m-d H:i:s');
        }
        Cache::put("Schedule::BilibiliSubscribe::mid_info::{$mid}", $vlist, Carbon::now()->addMinutes(3));
        return $vlist;
    }

    /**
     * @param string $link
     * @return array|null
     */
    private function getJson(string $link): ?array
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= "; Telegram-B23-Subscriber-Runner/$ts";
        return Http::
        withHeaders($headers)
            ->get($link)
            ->json();
    }

    /**
     * @param int $chat_id
     * @param int $mid
     * @return string|false
     */
    private function getLastSend(int $chat_id, int $mid): string|false
    {
        return Cache::get("Schedule::BilibiliSubscribe::last_send::{$chat_id}::{$mid}", false);
    }

    /**
     * @param int $chat_id
     * @param int $mid
     * @param string $bvid
     * @return bool
     */
    private function setLastSend(int $chat_id, int $mid, string $bvid): bool
    {
        return Cache::put("Schedule::BilibiliSubscribe::last_send::{$chat_id}::{$mid}", $bvid, Carbon::now()->addDay());
    }
}
