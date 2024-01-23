<?php

namespace App\Console\Schedule;

use App\Common\Config;
use App\Common\ERR;
use App\Jobs\SendPhotoJob;
use App\Models\TBilibiliSubscribes;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Throwable;

class BilibiliSubscribe extends Command
{
    use DispatchesJobs;

    protected $signature = 'subscribe:bilibili';
    protected $description = 'Get Subscribed UPs\' video lists then push to target chat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $datas = TBilibiliSubscribes::getAllSubscribe();
            foreach ($datas as $data) {
                sleep(10);
                unset($video, $message);
                $chat_id = $data['chat_id'];
                $mid = $data['mid'];
                self::info("Get subscribed UPs' video lists of $mid");
                $message = [
                    'chat_id' => $chat_id,
                    'caption' => '',
                ];
                $videoList = $this->getVideoList($mid);
                $last_send = $this->getLastSend($chat_id, $mid);
                if (!$last_send) {
                    self::info("Haven't send any video of $mid to $chat_id ");
                    $video = $videoList[0];
                } else {
                    for ($i = 0; $i < count($videoList); $i++) {
                        if ($i == 0 && $videoList[$i]['BVID'] == $last_send) {
                            self::info("There is no new video of $mid for $chat_id");
                            continue 2;
                        }
                        if ($i > 0 && $videoList[$i]['BVID'] == $last_send) {
                            self::info("Find new video of $mid for $chat_id");
                            $video = $videoList[$i - 1];
                            break;
                        }
                    }
                }
                if (isset($video)) {
                    //            $data['BVID'] = $video['bv_id'];
                    //            $data['AVID'] = 'av' . $video['id'];
                    //            $data['title'] = $video['title'];
                    //            $data['cover'] = $video['cover'];
                    //            $data['author'] = $video['upper']['name'];
                    //            $data['thumb_up'] = $video['cnt_info']['thumb_up'];
                    //            $data['coins'] = $video['cnt_info']['coin'];
                    //            $data['collect'] = $video['cnt_info']['collect'];
                    //            $data['play'] = $video['cnt_info']['play'];
                    //            $data['share'] = $video['cnt_info']['share'];
                    //            $data['comment'] = $video['cnt_info']['reply'];
                    //            $data['danmu'] = $video['cnt_info']['danmaku'];

                    self::info("New video of $mid is {$video['BVID']}");
                    $message['photo'] = $video['cover'];
                    $message['caption'] .= "视频: <b>{$video['title']}</b>\n";
                    $message['caption'] .= "UP主: <code>{$video['author']}</code>\n";
                    $message['caption'] .= "发布时间: <code>{$video['created']}</code>\n";
                    $message['caption'] .= "AV No.: <code>{$video['AVID']}</code>\n";
                    $message['caption'] .= "AV Link: <code>https://b23.tv/{$video['AVID']}</code>\n";
                    $message['caption'] .= "BV ID: <code>{$video['BVID']}</code>\n";
                    $message['caption'] .= "BV Link: <code>https://b23.tv/{$video['BVID']}</code>\n";
                    $message['caption'] .= "点赞、投币、收藏: {$video['thumb_up']}, {$video['coins']}, {$video['collect']}\n";
                    $message['caption'] .= "播放、分享: {$video['play']}, {$video['share']}\n";
                    $message['caption'] .= "评论、弹幕: {$video['comment']}, {$video['danmu']}\n";
                    $message['reply_markup'] = new InlineKeyboard([]);
                    $avButton = new InlineKeyboardButton([
                        'text' => "{$video['AVID']}",
                        'url' => "https://b23.tv/{$video['AVID']}",
                    ]);
                    $bvButton = new InlineKeyboardButton([
                        'text' => $video['BVID'],
                        'url' => "https://b23.tv/{$video['BVID']}",
                    ]);
                    $message['reply_markup']->addRow($avButton, $bvButton);
                    self::info("Send new video {$video['BVID']} of $mid to $chat_id");
                    $this->dispatch(new SendPhotoJob($message, 0));
                    $this->setLastSend($chat_id, $mid, $video['BVID']);
                }
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            ERR::log($e);
            return self::FAILURE;
        }
    }

    /**
     * @param int $mid
     * @return array
     */
    private function getVideoList(int $mid): array
    {
        $data = Cache::get("Schedule::BilibiliSubscribe::mid_info::$mid", false);
        if ($data) {
            return $data;
        }
        unset($data);
        $url = "https://api.bilibili.com/x/v2/medialist/resource/list?type=1&biz_id=$mid&ps=5";
        $json = $this->getJson($url);
        $media_list = $json['data']['media_list'];
        $vlist = [];
        foreach ($media_list as $video) {
            $data['BVID'] = $video['bv_id'];
            $data['AVID'] = 'av' . $video['id'];
            $data['title'] = $video['title'];
            $data['cover'] = $video['cover'];
            $data['author'] = $video['upper']['name'];
            $data['thumb_up'] = $video['cnt_info']['thumb_up'];
            $data['coins'] = $video['cnt_info']['coin'];
            $data['collect'] = $video['cnt_info']['collect'];
            $data['play'] = $video['cnt_info']['play'];
            $data['share'] = $video['cnt_info']['share'];
            $data['comment'] = $video['cnt_info']['reply'];
            $data['danmu'] = $video['cnt_info']['danmaku'];
            $data['created'] = Carbon::createFromTimestamp($video['pubtime'])->format('Y-m-d H:i:s');
            $vlist[] = $data;
        }
        Cache::put("Schedule::BilibiliSubscribe::mid_info::$mid", $vlist, Carbon::now()->addMinutes(15));
        return $vlist;
    }

    /**
     * @param string $link
     * @return array
     */
    private function getJson(string $link): array
    {
        self::info('Cache miss, get json from bilibili');
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-B23-Subscriber-Runner/$ts";
        return Http::
        withHeaders($headers)
            ->connectTimeout(10)
            ->timeout(10)
            ->retry(3, 1000, throw: false)
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
        return Cache::get("Schedule::BilibiliSubscribe::last_send::$chat_id::$mid", false);
    }

    /**
     * @param int $chat_id
     * @param int $mid
     * @param string $bvid
     * @return bool
     */
    private function setLastSend(int $chat_id, int $mid, string $bvid): bool
    {
        return Cache::put("Schedule::BilibiliSubscribe::last_send::$chat_id::$mid", $bvid, Carbon::now()->addMonths(3));
    }
}
