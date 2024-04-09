<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Addon License: https://www.desmg.com/policies/license
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

namespace App\Services\Keywords;

use App\Common\Config;
use App\Common\ERR;
use App\Jobs\SendMessageJob;
use App\Jobs\SendPhotoJob;
use App\Services\Base\BaseKeyword;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class B23VideoInfoKeyword extends BaseKeyword
{
    public string $name = 'Bilibili video info';
    public string $description = 'Get Bilibili Video Info';
    protected string $pattern = '/^(av(\d{1,19})|BV1[a-zA-Z0-9]{2}4[a-zA-Z0-9]1[a-zA-Z0-9]7[a-zA-Z0-9]{2})$/m';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $text = $message->getText() ?? $message->getCaption();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $video = $this->handle($text);
        if (is_array($video)) {
            $data = [
                'chat_id' => $chatId,
                'reply_to_message_id' => $messageId,
                'photo' => $video['photo'],
                'caption' => $video['caption'],
                'reply_markup' => $video['reply_markup'],
            ];
            $this->dispatch(new SendPhotoJob($data, 0));
        } else {
            $data['text'] = $video;
            $this->dispatch(new SendMessageJob($data, null, 0));
        }
    }

    private function handle(string $vid): string|array
    {
        $vid = $this->checkVid($vid);
        if (!$vid) {
            return 'Invalid AVID/BVID.';
        }
        try {
            return $this->getVideo($vid);
        } catch (Throwable $e) {
            ERR::log($e);
            return 'Get Video Info Failed.';
        }
    }

    private function checkVid(string $vid): false|string
    {
        if (preg_match('/^av(\d{1,19})$/m', $vid, $matches)) {
            return $matches[0];
        }
        if (preg_match('/^BV1[a-zA-Z0-9]{2}4[a-zA-Z0-9]1[a-zA-Z0-9]7[a-zA-Z0-9]{2}$/m', $vid, $matches)) {
            return $matches[0];
        }
        return false;
    }

    private function getVideo(string $vid): array
    {
        $video = $this->getData($vid);
        $video = $video['data'];
        $data['BVID'] = $video['bvid'];
        $data['AVID'] = 'av' . $video['aid'];
        $data['CID'] = $video['cid'];
        $data['title'] = $video['title'];
        $data['cover'] = $video['pic'];
        $data['author'] = $video['owner']['name'];
        $data['thumb_up'] = $video['stat']['like'];
        $data['coins'] = $video['stat']['coin'];
        $data['collect'] = $video['stat']['favorite'];
        $data['play'] = $video['stat']['view'];
        $data['share'] = $video['stat']['share'];
        $data['comment'] = $video['stat']['reply'];
        $data['danmu'] = $video['stat']['danmaku'];
        $data['created'] = Carbon::createFromTimestamp($video['pubdate'])->format('Y-m-d H:i:s');
        $message['photo'] = $data['cover'];
        $message['caption'] = "视频: <b>{$data['title']}</b>\n";
        $message['caption'] .= "UP主: <code>{$data['author']}</code>\n";
        $message['caption'] .= "发布时间: <code>{$data['created']}</code>\n";
        $message['caption'] .= "AV No.: <code>{$data['AVID']}</code>\n";
        $message['caption'] .= "AV Link: <code>https://b23.tv/{$data['AVID']}</code>\n";
        $message['caption'] .= "BV ID: <code>{$data['BVID']}</code>\n";
        $message['caption'] .= "BV Link: <code>https://b23.tv/{$data['BVID']}</code>\n";
        $message['caption'] .= "CID: <code>{$data['CID']}</code>\n";
        $message['caption'] .= "点赞、投币、收藏: {$data['thumb_up']}, {$data['coins']}, {$data['collect']}\n";
        $message['caption'] .= "播放、分享: {$data['play']}, {$data['share']}\n";
        $message['caption'] .= "评论、弹幕: {$data['comment']}, {$data['danmu']}\n";
        $message['reply_markup'] = new InlineKeyboard([]);
        $avButton = new InlineKeyboardButton([
            'text' => "{$data['AVID']}",
            'url' => "https://b23.tv/{$data['AVID']}",
        ]);
        $bvButton = new InlineKeyboardButton([
            'text' => $data['BVID'],
            'url' => "https://b23.tv/{$data['BVID']}",
        ]);
        $message['reply_markup']->addRow($avButton, $bvButton);
        return $message;
    }

    private function getData(string $vid): array
    {
        $vid = substr($vid, 2);
        $link = is_numeric($vid) ? "https://api.bilibili.com/x/web-interface/view?aid=$vid" : "https://api.bilibili.com/x/web-interface/view?bvid=$vid";
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-B23-Spider/$this->version";
        return Http::
        withHeaders($headers)
            ->get($link)
            ->json();
    }

    public function preExecute(Message $message): bool
    {
        $text = $message->getText() ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }
}
