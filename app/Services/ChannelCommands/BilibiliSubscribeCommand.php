<?php

namespace App\Services\ChannelCommands;

use App\Common\Config;
use App\Jobs\SendMessageJob;
use App\Models\TBilibiliSubscribes;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class BilibiliSubscribeCommand extends BaseCommand
{
    public string $name = 'bilibilisubscribe';
    public string $description = 'subscribe bilibili videos of an UP';
    public string $usage = '/bilibilisubscribe';
    public string $version = '2.0.0';

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
        if ($chatType !== 'channel') {
            $data['text'] .= "<b>Error</b>: This command is available only for channels.\n";
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
        $url = "https://api.bilibili.com/x/v2/medialist/resource/list?type=1&biz_id=$mid&ps=1";
        $json = $this->getJson($url);
        $author = $this->getAuthorName($json);
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
        //#endregion
        if (TBilibiliSubscribes::addSubscribe($chatId, $mid)) {
            $data['text'] .= "<b>Notice</b>: This feature will send a message to this chat when a new video is available.\n";
            $data['text'] .= str_repeat('=', 16) . "\n";
            $data['text'] .= "Author: <code>$author</code>\n";
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

    private function getAuthorName(array $json): string
    {
        return $json['data']['media_list'][0]['upper']['name'];
    }
}
