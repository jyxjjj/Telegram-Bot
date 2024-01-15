<?php

namespace App\Services\Commands;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Models\TBilibiliSubscribes;
use App\Models\TChatAdmins;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Carbon;
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
            $data['text'] .= "<b>Error</b>: This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Detect Admin Rights
        $admins = TChatAdmins::getChatAdmins($chatId);
        $userId = $message->getFrom()->getId();
        if (!in_array($userId, $admins, true)) {
            $data['text'] .= "<b>Error</b>: You should be an admin of this chat to use this command.\n\n";
            $data['text'] .= "<b>Warning</b>: This command can be used by people who was an admin before update admin list.\n\n";
            $data['text'] .= "<b>Notice</b>: Send /updatechatadministrators to update chat admin list.\n\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        //#region Check params
        if (!is_numeric($mid)) {
            $data['text'] .= "Invalid mid.\n";
            $data['text'] .= "<b>Usage</b>: /bilibilisubscribe mid.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion
        $url = "https://api.bilibili.com/x/space/arc/search?mid=$mid&ps=5&order=pubdate";
        $json = $this->getJson($url);
        //#region Check params by Server
        if ($json == null) {
            $data['text'] .= "Network error.\n";
            $data['text'] .= "Please retry.\n";
            $data['text'] .= "You can click the text below to copy your command.\n";
            $data['text'] .= "<code>/bilibilisubscribe $mid</code>\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        if ($json['code'] != 0) {
            $data['text'] .= "<b>Error</b>: Bilibili Server returned error.\n";
            $data['text'] .= "{$json['message']}\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        if ($json['data']['page']['count'] == 0) {
            $data['text'] .= "<b>Error</b>: No videos found.\n";
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
            $data['text'] .= "<b>Notice</b>: This function will send a message to this chat when a new video is available.\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "Tags: $tagNames\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "<b>First Video Info</b>:\n";
            $data['text'] .= "Name: <code>{$video['title']}</code>\n";
            $data['text'] .= "Author: <code>{$video['author']}</code>\n";
            $data['text'] .= "Created: <code>{$video['created']}</code>\n";
            $data['text'] .= "AV No.: <a href='https://www.bilibili.com/av{$video['aid']}'>{$video['aid']}</a>\n";
            $data['text'] .= "BV ID: <a href='https://www.bilibili.com/{$video['bvid']}'>{$video['bvid']}</a>\n";
            $data['text'] .= "Picture: <a href='{$video['pic']}'>View</a>\n";
            $data['text'] .= "Comments: {$video['comment']}\n";
            $data['text'] .= "Viewed Times: {$video['video_review']}\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "Subscribe successfully.\n";
        } else {
            $data['text'] .= "<b>Error</b>: Subscribe failed.\n";
            $data['text'] .= "One possibility is that this chat has already subscribed this mid.\n";
        }
        $this->dispatch(new SendMessageJob($data));
    }

    /**
     * @param string $link
     * @return array|null
     */
    private function getJson(string $link): ?array
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-B23-Subscriber/$this->version";
        return Http::
        withHeaders($headers)
            ->get($link)
            ->json();
    }
}
