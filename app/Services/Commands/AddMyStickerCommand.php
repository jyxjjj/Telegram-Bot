<?php

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Throwable;

class AddMyStickerCommand extends BaseCommand
{
    public string $name = 'addmysticker';
    public string $description = 'add sticker to pack';
    public string $usage = '/addmysticker';

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
        $userId = $message->getFrom()->getId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $reply_to_message = $message->getReplyToMessage();
        if (!$reply_to_message) {
            $data['text'] .= "<b>Error</b>: You should reply to a sticker for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        $stickerName = 'user_' . $userId . '_by_' . $telegram->getBotUsername();
        $sticker = $reply_to_message->getSticker();
        if (!$sticker) {
            $data['text'] .= "<b>Error</b>: Cannot get the sticker from the message you replied to.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        } else {
            $stickerFileId = $sticker->getFileId();
            $stickerEmoji = $sticker->getEmoji();
        }
        try {
            $serverResponse = Request::addStickerToSet([
                'user_id' => $userId,
                'name' => $stickerName,
                'emojis' => $stickerEmoji,
                'png_sticker' => $stickerFileId,
            ]);
            if ($serverResponse->isOk()) {
                $data['text'] .= "Sticker added successfully to <a href='https://t.me/addstickers/$stickerName'>this</a> sticker pack.\n";
            } else {
                if ($serverResponse->getDescription() == 'Bad Request: STICKERSET_INVALID') {
                    $data['text'] .= "It seems that you don't have a sticker pack yet.\nYou can create one by using /createmysticker command.\n";
                } else {
                    $data['text'] .= "<b>Error</b>: Add to your sticker pack failed.\n";
                    $data['text'] .= "<b>Error Code</b>: <code>{$serverResponse->getErrorCode()}</code>\n";
                    $data['text'] .= "<b>Error Msg</b>: <code>{$serverResponse->getDescription()}</code>\n\n";
                    $data['text'] .= "If you do not have a sticker pack created from this bot, send /createmysticker to create one.\n";
                }
            }
            $this->dispatch(new SendMessageJob($data));
        } catch (Throwable $e) {
            $data['text'] .= "An error occurred while add the sticker to your pack.\n";
            Log::error($e->getMessage(), $e->getTrace());
            $this->dispatch(new SendMessageJob($data));
        }
    }
}
