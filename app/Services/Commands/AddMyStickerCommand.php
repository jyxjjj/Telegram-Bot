<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
 *
 * Released under GNU General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Commands;

use App\Common\Config;
use App\Common\ERR;
use App\Jobs\DeleteTempStickerFileJob;
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use DESMG\RFC6986\Hash;
use Exception;
use GdImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Throwable;

class AddMyStickerCommand extends BaseCommand
{
    public string $name = 'addmysticker';
    public string $description = 'Add sticker to pack';
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
            $photos = $reply_to_message->getPhoto();
            if ($photos) {
                $this->getPhotos($photos);
                if (count($photos) <= 0) {
                    $data['text'] .= "<b>Error</b>: Cannot get photo from the message you replied to.\n";
                    $this->dispatch(new SendMessageJob($data));
                    return;
                }
                $stickerFileId = $photos[0]->getFileId();
                $is_png = true;
                $param = $message->getText(true);
                // regex of emoji
                $regex = '/^[\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{24C2}-\x{1F251}\x{1F900}-\x{1F9FF}\x{1F300}-\x{1F5FF}\x{1FA70}-\x{1FAF6}]$/u';
                if (preg_match($regex, $param)) {
                    $stickerEmoji = $param;
                } else {
                    $stickerEmoji = hex2bin('C2A9');
                }
            } else {
                $data['text'] .= "<b>Error</b>: Cannot get the sticker from the message you replied to.\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
        } else {
            $stickerEmoji = $sticker->getEmoji() ?? hex2bin('C2A9');
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
            ERR::log($e);
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion $stickerFileUrl
        //#region $stickerFileDownloaded
        try {
//            $stickerFileDownloaded = $this->downloadStickerFile($stickerFileUrl, $is_png, $is_tgs, $is_webm);
            $stickerFileDownloaded = $this->downloadStickerFile($stickerFileUrl);
            if (!$stickerFileDownloaded) {
                $data['text'] .= "<b>Error</b>: Downloading the sticker file failed.\n";
                $this->dispatch(new SendMessageJob($data));
                return;
            }
        } catch (Throwable $e) {
            if ($e->getCode() == -1) {
                $data['text'] .= "<b>Error</b>: {$e->getMessage()}.\n";
            } else {
                $data['text'] .= "An error occurred while downloading the sticker file.\n";
                ERR::log($e);
            }
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion $stickerFileDownloaded
        //#region addStickerToSet
        try {
            [$addStickerToSetSuccess, $serverResponse] =
                $this->addStickerToSet(
                    $userId, $stickerName, $stickerEmoji,
//                    $is_png, $is_tgs, $is_webm,
                    $stickerFileDownloaded
                );
            if (!$addStickerToSetSuccess) {
                switch ($serverResponse->getDescription()) {
                    case 'Bad Request: STICKERSET_INVALID':
                        $data['text'] .= "It seems that you don't have a sticker pack yet.\n";
                        $data['text'] .= "You can create one by using /createmysticker command.\n";
                        break;
                    case 'Bad Request: STICKER_PNG_NOPNG':
                        $data['text'] .= "The sticker file is not a PNG file.\n";
                        break;
                    case 'Bad Request: STICKER_PNG_DIMENSIONS':
                        $data['text'] .= "<b>Error</b>: The sticker is not 512x512.\n";
                        break;
                    default:
                        $data['text'] .= "<b>Error</b>: Add to your sticker pack failed.\n";
                        $data['text'] .= "<b>Error Code</b>: <code>{$serverResponse->getErrorCode()}</code>\n";
                        $data['text'] .= "<b>Error Msg</b>: <code>{$serverResponse->getDescription()}</code>\n\n";
                        $data['text'] .= "If you do not have a sticker pack created from this bot, send /createmysticker to create one.\n";
                        break;
                }
            } else {
                $data['text'] .= "Sticker added successfully to <a href='https://t.me/addstickers/$stickerName'>this</a> sticker pack.\n";
            }
            $this->dispatch(new SendMessageJob($data, null, 0));
            return;
        } catch (Throwable $e) {
            $data['text'] .= "An error occurred while add the sticker to your pack.\n";
            ERR::log($e);
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        //#endregion addStickerToSet
    }

    /**
     * @param array $photos
     * @return void
     */
    protected function getPhotos(array &$photos): void
    {
        usort($photos, function (PhotoSize $left, PhotoSize $right) {
            return bccomp(
                bcmul($right->getWidth(), $right->getHeight()),
                bcmul($left->getWidth(), $left->getHeight())
            );
        });
//                $photos = array_filter($photos, function (PhotoSize $photo) {
//                    return $photo->getWidth() <= 512 && $photo->getHeight() <= 512 && $photo->getFileSize() / 1024 <= 512;
//                });
//                $photos = array_values($photos);
    }

//    /**
//     * @param string $stickerFileUrl
//     * @param bool $is_png
//     * @param bool $is_tgs
//     * @param bool $is_webm
//     * @return string
//     */
//    private function downloadStickerFile(string $stickerFileUrl, bool $is_png, bool $is_tgs, bool $is_webm): string
//    {
//        $stickerFileData = Http::withHeaders(Config::CURL_HEADERS)
//            ->connectTimeout(3)
//            ->timeout(5)
//            ->retry(1, 1000)
//            ->get($stickerFileUrl);
//        if ($stickerFileData->ok()) {
//            $stickerFile = $stickerFileData->body();
//            $stickerFileExtension = 'png';
//            $is_tgs && $stickerFileExtension = 'tgs';
//            $is_webm && $stickerFileExtension = 'webm';
//            $stickerFileName = Hash::sha256($stickerFile) . '.' . $stickerFileExtension;
//            $path = "stickers/$stickerFileName";
//            Storage::disk('public')->put($path, $stickerFile);
//            $stickerFileDownloaded = Storage::disk('public')->path($path);
//            $this->dispatch(new DeleteTempStickerFileJob($path));
//            return $stickerFileDownloaded;
//        } else {
//            return '';
//        }
//
//    }

    /**
     * @param string $stickerFileId
     * @return array
     */
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

//    /**
//     * @throws TelegramException
//     */
//    private function addStickerToSet(int $userId, string $stickerName, string $stickerEmoji, bool $is_png, bool $is_tgs, bool $is_webm, string $stickerFileDownloaded): array
//    {
//        $stickerInputFile = Request::encodeFile($stickerFileDownloaded);
//        $stickerRequestData = [
//            'user_id' => $userId,
//            'name' => $stickerName,
//            'emojis' => $stickerEmoji,
//        ];
//        $is_png && $stickerRequestData['png_sticker'] = $stickerInputFile;
//        $is_tgs && $stickerRequestData['tgs_sticker'] = $stickerInputFile;
//        $is_webm && $stickerRequestData['webm_sticker'] = $stickerInputFile;
//        $serverResponse = Request::addStickerToSet($stickerRequestData);
//        if ($serverResponse->isOk()) {
//            return [true, $serverResponse];
//        } else {
//            return [false, $serverResponse];
//        }
//    }

    /**
     * @param string $stickerFileUrl
     * @return string
     * @throws Exception
     */
    private function downloadStickerFile(string $stickerFileUrl): string
    {
        $stickerFileData = Http::withHeaders(Config::CURL_HEADERS)
            ->connectTimeout(3)
            ->timeout(5)
            ->retry(1, 1000, throw: false)
            ->get($stickerFileUrl);
        if ($stickerFileData->ok()) {
            $stickerFile = $stickerFileData->body();
            $stickerFileName = Hash::sha256($stickerFile) . '.png';
            $path = "stickers/$stickerFileName";
            $fullPath = Storage::disk('public')->path($path);
            $this->resizeImage($fullPath, $stickerFile);
            if (!Storage::disk('public')->exists($path)) {
                throw new Exception('Sticker file downloaded but not found', -1);
            }
            if (Storage::disk('public')->size($path) > 512000) {
                throw new Exception('Sticker file resized to 512x512 but size is still more than 512KB', -1);
            }
            $this->dispatch(new DeleteTempStickerFileJob($path));
            return $fullPath;
        } else {
            return '';
        }

    }

    /**
     * @param string $path
     * @param string $imageData
     * @return void
     * @throws Exception
     */
    private function resizeImage(string $path, string $imageData): void
    {
        $imageData = imagecreatefromstring($imageData);
        $imageWidth = imagesx($imageData);
        $imageHeight = imagesy($imageData);
        if ($imageWidth !== 512 || $imageHeight !== 512) {
            if ($imageWidth !== $imageHeight) {
                //#region get an 512x512 transparent image $newImage
                $newImage = $this->createTransparentImage(512, 512);
                //#endregion
                if ($imageWidth > $imageHeight) {
                    $newImageData = imagescale($imageData, 512);
                    imagedestroy($imageData);
                    $imageData = $newImageData;
                    imagedestroy($newImageData);
                    $imageWidth = imagesx($imageData);
                    $imageHeight = imagesy($imageData);
                    $x = 0;
                    $y = (512 - $imageHeight) / 2;
                } else {
                    $ratio = $imageWidth / $imageHeight;
                    $newImageData = imagescale($imageData, (int)(512 * $ratio), 512);
                    imagedestroy($imageData);
                    $imageData = $newImageData;
                    imagedestroy($newImageData);
                    $imageWidth = imagesx($imageData);
                    $imageHeight = imagesy($imageData);
                    $x = (512 - $imageWidth) / 2;
                    $y = 0;
                }
                imagecopy($newImage, $imageData, (int)$x, (int)$y, 0, 0, $imageWidth, $imageHeight);
                $imageData = $newImage;
                imagedestroy($newImage);
            }
            $newImageData = imagescale($imageData, 512, 512);
            imagedestroy($imageData);
            $imageData = $newImageData;
            imagedestroy($newImageData);
            if (!$imageData) {
                throw new Exception('Sticker file cannot be resized to 512x512', -1);
            }
        }
        imagesavealpha($imageData, true);
        imagepng($imageData, $path);
        imagedestroy($imageData);
    }

    /**
     * @param int $width
     * @param int $height
     * @return GdImage
     * @noinspection PhpSameParameterValueInspection
     */
    private function createTransparentImage(int $width, int $height): GdImage
    {
        $newImage = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        imagefill($newImage, 0, 0, $transparent);
        return $newImage;
    }

    /**
     * @param int $userId
     * @param string $stickerName
     * @param string $stickerEmoji
     * @param string $stickerFileDownloaded
     * @return array
     * @throws TelegramException
     */
    private function addStickerToSet(int $userId, string $stickerName, string $stickerEmoji, string $stickerFileDownloaded): array
    {
        $stickerInputFile = Request::encodeFile($stickerFileDownloaded);
        $stickerRequestData = [
            'user_id' => $userId,
            'name' => $stickerName,
            'emojis' => $stickerEmoji,
            'png_sticker' => $stickerInputFile,
        ];
        $serverResponse = Request::addStickerToSet($stickerRequestData);
        if ($serverResponse->isOk()) {
            return [true, $serverResponse];
        } else {
            return [false, $serverResponse];
        }
    }
}
