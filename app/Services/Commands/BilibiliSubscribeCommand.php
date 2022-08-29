<?php

namespace App\Services\Commands;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Models\TBilibiliSubscribes;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BilibiliSubscribeCommand extends BaseCommand
{
    public string $name = 'bilibilisubscribe';
    public string $description = 'subscribe bilibili videos of an UP';
    public string $usage = '/bilibilisubscribe';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        $mid = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        //#region Detect Chat Type
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data['text'] .= "*Error:* This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Detect Admin Rights
        $admins = TChatAdmins::getChatAdmins($chatId);
        $userId = $message->getFrom()->getId();
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "*Error:* You should be an admin of this chat to use this command.\n\n";
            $data['text'] .= "*Warning:* This command can be used by people who was an admin before update admin list.\n\n";
            $data['text'] .= "*Notice:* Send /updatechatadministrators to update chat admin list.\n\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Check params
        if (!is_numeric($mid)) {
            $data['text'] .= "Invalid mid.\n";
            $data['text'] .= "*Usage:* /bilibilisubscribe mid.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        $url = "https://api.bilibili.com/x/space/arc/search?mid={$mid}&ps=5&order=pubdate";
        $json = $this->getJson($url);
        //#region Check params by Server
        if ($json == null) {
            $data['text'] .= "Network error.\n";
            $data['text'] .= "Please retry.\n";
            $data['text'] .= "You can click the text below to copy your command.\n";
            $data['text'] .= "`/bilibilisubscribe {$mid}`\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        if ($json['code'] != 0) {
            $data['text'] .= "*Error:* Bilibili Server returned error.\n";
            $data['text'] .= "{$json['message']}\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        if ($json['data']['page']['count'] == 0) {
            $data['text'] .= "*Error:* No videos found.\n";
            $data['text'] .= "This function only support to subscribe an UP who has already submitted at least one video.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        $tlist = $json['data']['list']['tlist'];
        $vlist = $json['data']['list']['vlist'];
        $tagNames = [];
        foreach ($tlist as $tag) {
            $tagNames[] = $tag['name'];
        }
        $tagNames = implode(', ', $tagNames);
        $video = $vlist[0];
        $video['created'] = Carbon::createFromTimestamp($video['created'])->format('Y-m-d H:i:s');
        if (TBilibiliSubscribes::addSubscribe($chatId, $mid)) {
            $data['text'] .= "*Notice:* This function will send a message to this chat when a new video is available.\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "Tags: $tagNames\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "*First Video Info:*\n";
            $data['text'] .= "Name: `{$video['title']}`\n";
            $data['text'] .= "Author: `{$video['author']}`\n";
            $data['text'] .= "Created: `{$video['created']}`\n";
            $data['text'] .= "AV No.: [{$video['aid']}](https://www.bilibili.com/{$video['aid']})\n";
            $data['text'] .= "BV ID: [{$video['bvid']}](https://www.bilibili.com/{$video['bvid']})\n";
            $data['text'] .= "Picture: [View]({$video['pic']})\n";
            $data['text'] .= "Comments: {$video['comment']}\n";
            $data['text'] .= "Viewed Times: {$video['video_review']}\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "Subscribe successfully.\n";
        } else {
            $data['text'] .= "*Error:* Subscribe failed.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }

    private function getJson(string $link): ?array
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= "; Telegram-B23-Subscriber/$this->version";
        return Http::
        withHeaders($headers)
            ->get($link)
            ->json();
    }
}
