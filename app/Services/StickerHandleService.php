<?php

namespace App\Services;

//use App\Jobs\DownloadStickerJob;
use App\Services\Base\BaseService;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class StickerHandleService extends BaseService
{
    /**
     * @param Message  $message
     * @param Telegram $telegram
     * @param int      $updateId
     * @return bool
     */
    public function handle(Message $message, Telegram $telegram, int $updateId): bool
    {
        $fileId = $message->getSticker()->getFileId();
//        $this->dispatch(new DownloadStickerJob(['file_id' => $fileId,]));
        return false;
    }
}
