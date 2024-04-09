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
use App\Jobs\SendMessageJob;
use App\Services\Base\BaseKeyword;
use Illuminate\Support\Facades\Http;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;

class B23TrackerRemoverKeyword extends BaseKeyword
{
    public string $name = 'b23 tracker remover';
    public string $description = 'Remove b23 tracker from b23 link';
    public string $version = '2.0.1';
    protected string $pattern = '/(b23\.tv|bilibili\.com)/';

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        $chatId = $message->getChat()->getId();
        $messageId = $message->getMessageId();
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];
        $data['text'] .= "Bilibili Tracker Remover v$this->version\n\n";
        $entities = $this->getUrls($message);
        $this->handle($entities, $data);
        $data['text'] .= "\nCopy and send Video ID to get video info.";
        isset($data['reply_markup']) && $this->dispatch(new SendMessageJob($data, null, 0));
    }

    /**
     * @param Message $message
     * @return array
     */
    protected function getUrls(Message $message): array
    {
        $text = $message->getText() ?? $message->getCaption();
        $entities = $message->getEntities() ?? $message->getCaptionEntities() ?? [];
        $urls = [];
        foreach ($entities as $entity) {
            if ($entity->getType() === 'url') {
                $offset = $entity->getOffset();
                $length = $entity->getLength();
                $url = mb_substr($text, $offset, $length);
                if (!str_starts_with($url, 'http')) {
                    $url = 'https://' . $url;
                }
                $urls[] = $url;
            }
            if ($entity->getType() === 'text_link') {
                $url = $entity->getUrl();
                if (!str_starts_with($url, 'http')) {
                    $url = 'https://' . $url;
                }
                $urls[] = 'https://' . $url;
            }
        }
        return array_filter($urls);
    }

    /**
     * @param array $entities
     * @param array $data
     * @return void
     */
    private function handle(array $entities, array &$data): void
    {
        if (count($entities) > 3) {
            $entities = array_slice($entities, 0, 3);
        }
        foreach ($entities as $entity) {
            $url = parse_url($entity);
            if (!$url) {
                return;
            }
            if ($url['scheme'] == 'http') {
                $entity = str_replace('http://', 'https://', $entity);
            }
            if (!in_array($url['host'], ['b23.tv', 'bilibili.com', 'www.bilibili.com', 'live.bilibili.com', 'space.bilibili.com', 'm.bilibili.com'])) {
                continue;
            }
            $changedLocation = false;
            if ($url['host'] == 'b23.tv' || $url['host'] == 'm.bilibili.com') {
                $entity = $this->getLocation($entity);
                $changedLocation = true;
            }
            $changedMobileCV = false;
            if (str_starts_with($entity, 'https://www.bilibili.com/read/mobile')) {
                $entity = $this->replaceReadMobileToCVLink($entity);
                $changedMobileCV = true;
            }
            $link = $this->removeAllParams($entity);
            if ($link == $entity && !$changedLocation && !$changedMobileCV) {
                continue;
            }
            unset($changedLocation, $changedMobileCV);
            if (str_starts_with($link, 'https://www.bilibili.com/video/')) {
                $id = $this->getAVBV($link);
            }
            if (str_starts_with($link, 'https://www.bilibili.com/read/')) {
                $id = $this->getCV($link);
            }
            if (str_starts_with($link, 'https://live.bilibili.com/')) {
                $id = $this->getLiveID($link);
            }
            if (str_starts_with($link, 'https://space.bilibili.com/')) {
                $id = $this->getUID($link);
            }
            if (!empty($id) && (str_starts_with($id, 'av') || str_starts_with($id, 'BV'))) {
                $data['text'] .= "Click to copy Video ID: <code>$id</code>\n";
            }
            $data['text'] .= "<code>$link</code>\n";
            $button = new InlineKeyboardButton([
                'text' => $id ?? $link,
                'url' => $link,
            ]);
            !isset($data['reply_markup']) && $data['reply_markup'] = new InlineKeyboard([]);
            $data['reply_markup']->addRow($button);
        }
    }

    /**
     * @param string $link
     * @return string header Location
     */
    private function getLocation(string $link): string
    {
        $headers = Config::CURL_HEADERS;
        $headers['User-Agent'] .= " Telegram-B23-Link-Tracker-Remover/$this->version";
        $location = Http::
        withHeaders($headers)
            ->withoutRedirecting()
            ->get($link)
            ->header('Location');
        return $location ?: $link;
    }

    /**
     * @param string $link
     * @return string
     */
    private function replaceReadMobileToCVLink(string $link): string
    {
        $id = $link;
        if (preg_match('/([0-9]+)\?plat_id=/', $link, $matches)) {
            $id = 'https://www.bilibili.com/read/cv' . $matches[1];
        }
        return $id;
    }

    /**
     * @param string $link
     * @return string
     */
    private function removeAllParams(string $link): string
    {
        $url = parse_url($link);
        $query = $url['query'] ?? '';
        $query = explode('&', $query);
        $query = array_filter($query, function ($item) {
            return $item !== '' && preg_match('/^p=([0-9]{1,3})$/', $item);
        });
        $count = count($query);
        $query = implode('&', $query);
        $url['query'] = $query;
        $length = strlen($query);
        return $count && $length ? "https://{$url['host']}{$url['path']}?{$url['query']}" : "{$url['scheme']}://{$url['host']}{$url['path']}";
    }

    /**
     * @param string $link
     * @return string
     */
    private function getAVBV(string $link): string
    {
        $id = $link;
        if (preg_match('/av(\d{1,19})/', $link, $matches)) {
            $id = 'av' . $matches[1];
        } elseif (preg_match('/BV(1[a-zA-Z0-9]{2}4[a-zA-Z0-9]1[a-zA-Z0-9]7[a-zA-Z0-9]{2})/', $link, $matches)) {
            $id = 'BV' . $matches[1];
        }
        return $id;
    }

    /**
     * @param string $link
     * @return string
     */
    private function getCV(string $link): string
    {
        $id = $link;
        if (preg_match('/cv([0-9]+)/', $link, $matches)) {
            $id = 'cv' . $matches[1];
        }
        return $id;
    }

    /**
     * @param string $link
     * @return string
     */
    private function getLiveID(string $link): string
    {
        $id = $link;
        if (preg_match('/live\.bilibili\.com\/(h5\/)?(\d+)/', $link, $matches)) {
            $id = 'LiveID: ' . $matches[2];
        }
        return $id;
    }

    /**
     * @param string $link
     * @return string
     */
    private function getUID(string $link): string
    {
        $id = $link;
        if (preg_match('/space\.bilibili\.com\/(\d+)/', $link, $matches)) {
            $id = 'UID: ' . $matches[1];
        }
        return $id;
    }

    public function preExecute(Message $message): bool
    {
        $text = $message->getText(true) ?? $message->getCaption();
        return $text && preg_match($this->pattern, $text);
    }

    /**
     * @deprecated 2.0.0
     */
    private function old(): void
    {
        $pattern = '/(https?:\/\/)?(b23\.tv|(www\.|live\.|space\.)?bilibili\.com)\/(video\/|read\/|mobile\/)?[a-zA-Z0-9]+(\?p=([0-9]{1,3}))?/';
        $pattern_av = '/https:\/\/www\.bilibili\.com\/(video\/)?(av|AV)\d+/';
        $pattern_bv = '/https:\/\/www\.bilibili\.com\/(video\/)?(bv|BV)[a-zA-Z\d]+/';
        $pattern_cv = '/https:\/\/www\.bilibili\.com\/(read\/)?(mobile\/)?(cv|CV)?\d+/';
        $pattern_space = '/https:\/\/space\.bilibili\.com\/\d+/';
        $pattern_live = '/https:\/\/live\.bilibili\.com\/[a-zA-Z\d]+/';
    }
}
