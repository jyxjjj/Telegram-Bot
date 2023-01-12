<?php

namespace App\Services\Commands;

use App\Common\Config;
use App\Jobs\DeleteTempStickerFileJob;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use DESMG\RFC6986\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            $stickerEmoji = $sticker->getEmoji();
            $is_tgs = $sticker->getIsAnimated();
            $is_webm = $sticker->getIsVideo();
            $is_png = $is_tgs == false && $is_webm == false;
            $stickerFileId = $sticker->getFileId();
        }
        /**
         * TODO: maybe we should create 3 sticker sets to support all types of stickers
         */
        if (!$is_png) {
            $data['text'] .= "<b>Error</b>: Only PNG sticker is supported.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#region $stickerFileUrl
        try {
            [$stickerFileUrl, $stickerFile] = $this->getStickerFileURL($stickerFileId);
            if (!$stickerFileUrl) {
                $data['text'] .= "<b>Error</b>: Get sticker file path failed.\n";
                $data['text'] .= "<b>Error Code</b>: <code>{$stickerFile->getErrorCode()}</code>\n";
                $data['text'] .= "<b>Error Msg</b>: <code>{$stickerFile->getDescription()}</code>\n\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
        } catch (Throwable $e) {
            $data['text'] .= "An error occurred while getting sticker file path.\n";
            Log::error($e->getMessage(), $e->getTrace());
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion $stickerFileUrl
        //#region $stickerFileDownloaded
        try {
            $stickerFileDownloaded = $this->downloadStickerFile($stickerFileUrl, $is_png, $is_tgs, $is_webm);
            if (!$stickerFileDownloaded) {
                $data['text'] .= "<b>Error</b>: Downloading the sticker file failed.\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
        } catch (Throwable $e) {
            $data['text'] .= "An error occurred while downloading the sticker file.\n";
            Log::error($e->getMessage(), $e->getTrace());
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion $stickerFileDownloaded
        //#region addStickerToSet
        try {
            [$addStickerToSetSuccess, $serverResponse] =
                $this->addStickerToSet(
                    $userId, $stickerName, $stickerEmoji,
                    $is_png, $is_tgs, $is_webm,
                    $stickerFileDownloaded
                );
            if (!$addStickerToSetSuccess) {
                if ($serverResponse->getDescription() == 'Bad Request: STICKERSET_INVALID') {
                    $data['text'] .= "It seems that you don't have a sticker pack yet.\nYou can create one by using /createmysticker command.\n";
                } else {
                    $data['text'] .= "<b>Error</b>: Add to your sticker pack failed.\n";
                    $data['text'] .= "<b>Error Code</b>: <code>{$serverResponse->getErrorCode()}</code>\n";
                    $data['text'] .= "<b>Error Msg</b>: <code>{$serverResponse->getDescription()}</code>\n\n";
                    $data['text'] .= "If you do not have a sticker pack created from this bot, send /createmysticker to create one.\n";
                }
            } else {
                $data['text'] .= "Sticker added successfully to <a href='https://t.me/addstickers/$stickerName'>this</a> sticker pack.\n";
            }
            $this->dispatch(new SendMessageJob($data));
            return;
        } catch (Throwable $e) {
            $data['text'] .= "An error occurred while add the sticker to your pack.\n";
            Log::error($e->getMessage(), $e->getTrace());
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion addStickerToSet
    }

    private function getStickerFileURL(string $stickerFileId): array
    {
        $stickerFile = Request::getFile(['file_id' => $stickerFileId]);
        if ($stickerFile->isOk()) {
            $stickerFile = $stickerFile->getResult();
            $stickerFilePath = $stickerFile->getFilePath();
            return [env('TELEGRAM_API_BASE_URI') . '/file/bot' . env('TELEGRAM_BOT_TOKEN') . '/' . $stickerFilePath, $stickerFile];
        } else {
            return ['', $stickerFile];
        }
    }

    private function downloadStickerFile(string $stickerFileUrl, bool $is_png, bool $is_tgs, bool $is_webm): string
    {
        $stickerFileData = Http::withHeaders(Config::CURL_HEADERS)
            ->connectTimeout(3)
            ->timeout(5)
            ->retry(1, 1000)
            ->get($stickerFileUrl);
        if ($stickerFileData->ok()) {
            $stickerFile = $stickerFileData->body();
            $stickerFileExtension = 'png';
            $is_tgs && $stickerFileExtension = 'tgs';
            $is_webm && $stickerFileExtension = 'webm';
            $stickerFileName = Hash::sha256($stickerFile) . '.' . $stickerFileExtension;
            $path = "stickers/$stickerFileName";
            Storage::disk('public')->put($path, $stickerFile);
            $stickerFileDownloaded = Storage::disk('public')->path($path);
            $this->dispatch(new DeleteTempStickerFileJob($path));
            return $stickerFileDownloaded;
        } else {
            return '';
        }

    }

    private function addStickerToSet(int $userId, string $stickerName, string $stickerEmoji, bool $is_png, bool $is_tgs, bool $is_webm, string $stickerFileDownloaded): array
    {
        $stickerInputFile = Request::encodeFile($stickerFileDownloaded);
        $stickerRequestData = [
            'user_id' => $userId,
            'name' => $stickerName,
            'emojis' => $stickerEmoji,
        ];
        $is_png && $stickerRequestData['png_sticker'] = $stickerInputFile;
        $is_tgs && $stickerRequestData['tgs_sticker'] = $stickerInputFile;
        $is_webm && $stickerRequestData['webm_sticker'] = $stickerInputFile;
        $serverResponse = Request::addStickerToSet($stickerRequestData);
        if ($serverResponse->isOk()) {
            return [true, $serverResponse];
        } else {
            return [false, $serverResponse];
        }
    }
}
