<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2025 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2025 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Commands;

use App\Common\RequestHelper;
use App\Jobs\DeleteFileJob;
use App\Jobs\SendMessageJob;
use App\Jobs\SendPhotoJob;
use App\Services\Base\BaseCommand;
use DESMG\RFC6986\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class PixivCommand extends BaseCommand
{
    public string $name = 'pixiv';
    public string $description = 'Get a pic from pixiv';
    public string $usage = '/pixiv';

    /**
     * @param Message $message
     * @param Telegram $telegram
     * @param int $updateId
     * @return void
     */
    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => "<b>Error</b>: This command is available only for groups.\n",
            ];
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        if (Cache::has('PixivCommand')) {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => "<b>Error</b>: This command can be only used once per minute.\n",
            ];
            $this->dispatch(new SendMessageJob($data));
            return;
        }
        Cache::put('PixivCommand', 1, Carbon::now()->addMinute());
        [$photo, $caption] = $this->getPhotoAndCaption();
        if ($photo && $caption) {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'photo' => $photo,
                'is_file' => true,
                'caption' => $caption,
                'protect_content' => true,
            ];
            $this->dispatch(new SendPhotoJob($data, 0));
        } else {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'text' => "<b>Error</b>: Get a random picture failed.\n",
            ];
            $this->dispatch(new SendMessageJob($data));
        }
    }

    private function getPhotoAndCaption(): array
    {
        $storage = Storage::disk('public');
        $now = Carbon::now();
        $date = $now->clone()->format('Y-m-d');
        $path = "pixiv/$date.json";
        if (!$storage->exists($path)) {
            $date = $now->clone()->subDay()->format('Y-m-d');
            $path = "pixiv/$date.json";
            if (!$storage->exists($path)) {
                $date = $now->clone()->subDays(2)->format('Y-m-d');
                $path = "pixiv/$date.json";
                if (!$storage->exists($path)) {
                    return [null, null];
                }
            }
        }
        $json = $storage->get($path);
        $data = json_decode($json, true);
        $date = $data['date'];
        $index = array_rand($data['data']);
        $item = $data['data'][$index];
        $title = $item['title'];
        $artwork_url = $item['artwork_url'];
        $author = $item['author'];
        $author_url = $item['author_url'];
        $url = $item['url'];
        $caption = "⚠️ #NSFW\n";
        $caption .= "Artwork: <a href='$artwork_url'>$title</a>\n";
        $caption .= "Author: <a href='$author_url'>$author</a>\n";
        $caption .= "Date: $date\n\n";
        $caption .= "⚠️Content has its own copyright\n";
        $caption .= "DMCA Request: @jyxjjj\n";
        $caption .= "⚠️ #NSFW\n";
        try {
            $path = $this->download($url);
            if ($path) {
                return [$path, $caption];
            }
            return [null, null];
        } catch (Throwable) {
            return [null, null];
        }
    }

    private function download($url): ?string
    {
        $headers['Referer'] = 'https://www.pixiv.net/ranking.php?mode=daily';
        $response = RequestHelper::getInstance()
            ->withHeaders($headers)
            ->get($url);
        if ($response->ok()) {
            $body = $response->body();
            $name = Hash::sha256($body);
            $path = "pixiv/$name.jpg";
            Storage::disk('public')->put($path, $body);
            $this->dispatch(new DeleteFileJob($path));
            return Storage::disk('public')->path($path);
        }
        return null;
    }
}
