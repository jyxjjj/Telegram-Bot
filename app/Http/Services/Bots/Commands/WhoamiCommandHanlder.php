<?php

namespace App\Http\Services\Bots\Commands;

use App\Http\Services\Bots\BotCommon;
use App\Http\Services\Bots\Jobs\SendPhotoJob;
use App\Jobs\BaseQueue;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\UserProfilePhotos;
use Longman\TelegramBot\Request;

class WhoamiCommandHanlder extends BaseQueue
{
    private Message $message;

    public function __construct(Message $message)
    {
        parent::__construct();
        $this->message = $message;
    }

    public function handle()
    {
        new BotCommon;
        $message = $this->message;
        $from = $message->getFrom();
        $user_id = $from->getId();
        $chat_id = $message->getChat()->getId();
        $message_id = $message->getMessageId();
        $data = [
            'chat_id' => $chat_id,
            'reply_to_message_id' => $message_id,
            'disable_web_page_preview' => true,
            'allow_sending_without_reply' => true,
        ];
        $caption = <<<EOF
用户ID: {$user_id}
昵称: {$from->getFirstName()} {$from->getLastName()}
用户名: {$from->getUsername()}
EOF;
        $user_profile_photos_response = Request::getUserProfilePhotos([
            'user_id' => $user_id,
            'limit' => 1,
            'offset' => null,
        ]);
        if ($user_profile_photos_response->isOk()) {
            /** @var UserProfilePhotos $user_profile_photos */
            $user_profile_photos = $user_profile_photos_response->getResult();
            if ($user_profile_photos->getTotalCount() > 0) {
                $photos = $user_profile_photos->getPhotos();
                $photo = end($photos[0]);
                $file_id = $photo->getFileId();
                $data['photo'] = $file_id;
                $data['caption'] = $caption;
                SendPhotoJob::dispatch($data);
                return;
            }
        }
        $data['text'] = $caption;
        SendPhotoJob::dispatch($data);
    }
}
