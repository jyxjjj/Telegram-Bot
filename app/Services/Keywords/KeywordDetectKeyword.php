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
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Keywords;

use App\Common\ERR;
use App\Common\Texts\ObfuscatedTextNormalizer;
use App\Jobs\BanMemberJob;
use App\Jobs\DeleteMessageJob;
use App\Jobs\RestrictMemberJob;
use App\Jobs\SendMessageJob;
use App\Models\TChatAdmins;
use App\Models\TChatKeywords;
use App\Models\TChatKeywordsOperationEnum;
use App\Models\TChatKeywordsTargetEnum;
use App\Models\TChatKeywordsWhiteLists;
use App\Services\Base\BaseKeyword;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Telegram;
use Throwable;

class KeywordDetectKeyword extends BaseKeyword
{
    public string $name = 'Keyword Detecter';
    public string $description = 'Match Keywords';
    protected string $pattern = '//';
    private bool $stop = false;

    public function execute(Message $message, Telegram $telegram, int $updateId): void
    {
        /** @var Collection<TChatKeywords> $keywords */
        $keywords = TChatKeywords::getKeywords($message->getChat()->getId());
        $keywords = $keywords->sortBy(
            static fn(TChatKeywords $keyword): int => $keyword->operation === TChatKeywordsOperationEnum::OPERATION_BAN ? 0 : 1
        );
        foreach ($keywords as $keyword) {
            try {
                $this->handle($keyword->keyword, $keyword->target, $keyword->operation, $keyword->data, $message);
            } catch (Throwable $e) {
                ERR::log($e);
            }
            if ($this->stop) {
                break;
            }
        }
    }

    public function preExecute(Message $message): bool
    {
        return true;
    }

    private function handle(
        string                     $keyword,
        TChatKeywordsTargetEnum    $target,
        TChatKeywordsOperationEnum $operation,
        array                      $data,
        Message                    $message
    ): void
    {
        switch ($target) {
            case TChatKeywordsTargetEnum::TARGET_CHATID:
                $chatId = $message->getChat()->getId();
                if ($chatId == $keyword) {
                    $this->runOperation($operation, $data, $message);
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_USERID:
                $userId = $message->getFrom()->getId();
                if ($userId == $keyword) {
                    $this->runOperation($operation, $data, $message);
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_NAME:
            case TChatKeywordsTargetEnum::TARGET_TEXT:
                $value = match ($target) {
                    TChatKeywordsTargetEnum::TARGET_NAME =>
                        ($message->getFrom()->getFirstName() ?? '') . ($message->getFrom()->getLastName() ?? ''),
                    TChatKeywordsTargetEnum::TARGET_TEXT =>
                        $message->getText() ?? $message->getCaption() ?? '',
                    default => '',
                };
                if (ObfuscatedTextNormalizer::matches($value, $keyword)) {
                    $this->runOperation($operation, $data, $message);
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_FROMNAME:
            case TChatKeywordsTargetEnum::TARGET_TITLE:
                break;
            case TChatKeywordsTargetEnum::TARGET_DICE:
                if ($message->getDice()) {
                    $text = $message->getDice()->getEmoji() ?? '';
                    if (strtoupper(bin2hex($text)) == strtoupper($keyword)) {
                        $this->runOperation($operation, $data, $message);
                    }
                }
                break;
            case TChatKeywordsTargetEnum::TARGET_STICKER:
                if ($message->getSticker()) {
                    $fileUniqueId = $message->getSticker()->getFileUniqueId() ?? '';
                    if ($fileUniqueId == $keyword) {
                        $this->runOperation($operation, $data, $message);
                    }
                }
        }
    }

    private function runOperation(
        TChatKeywordsOperationEnum $operation,
        array                      $data,
        Message                    $message
    ): void
    {
        switch ($operation) {
            case TChatKeywordsOperationEnum::OPERATION_BAN:
                $this->ban($data, $message);
                $this->stop = true;
                break;
            case TChatKeywordsOperationEnum::OPERATION_DELETE:
                $this->delete($data, $message);
                $this->stop = true;
                break;
            case TChatKeywordsOperationEnum::OPERATION_FORWARD:
                $this->forward($data, $message);
                break;
            case TChatKeywordsOperationEnum::OPERATION_REPLY:
                $this->reply($data, $message);
                break;
            case TChatKeywordsOperationEnum::OPERATION_RESTRICT:
                $this->restrict($data, $message);
                $this->stop = true;
                break;
            default:
                break;
        }
    }

    private function delete(array $data, Message $message): void
    {
        if ($this->isProtected($message, true)) {
            return;
        }
        if (!$this->deleteMessage($message)) {
            return;
        }
        $this->sendOperationMessage($data, $message);
    }

    private function ban(array $data, Message $message): void
    {
        if ($this->isProtected($message, true)) {
            return;
        }
        $cacheKey = "Keyword::BAN::{$message->getChat()->getId()}::{$message->getFrom()->getId()}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, 1, Carbon::now()->addMinute());
        $this->deleteMessage($message);
        $this->dispatch(new BanMemberJob([
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
            'user_id' => $message->getFrom()->getId(),
        ]));
        $this->sendOperationMessage($data, $message);
    }

    private function restrict(array $data, Message $message): void
    {
        if ($this->isProtected($message, true)) {
            return;
        }

        $delete = $data['delete'] ?? true;
        $time = $data['time'] ?? 86400;
        $text = $data['text'] ?? null;

        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $cacheKey = "Keyword::RESTRICT::$chatId::$userId";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, 1, Carbon::now()->addMinute());

        if ($delete) {
            $this->deleteMessage($message);
        }
        $this->dispatch(new RestrictMemberJob([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => Carbon::now()->addSeconds($time)->timestamp,
        ]));
        if ($text !== null) {
            $this->sendOperationMessage(['text' => $text], $message);
        }
    }

    private function sendOperationMessage(array $data, Message $message): void
    {
        if (!isset($data['text']) || !is_string($data['text'])) {
            return;
        }
        $this->dispatch(new SendMessageJob([
            'chat_id' => $message->getChat()->getId(),
            'text' => $this->renderTemplate($data['text'], $message),
        ], null, 0));
    }

    private function renderTemplate(string $template, Message $message): string
    {
        $userId = $message->getFrom()->getId();

        return strtr($template, [
            '{{userlink}}' => "<a href=\"tg://user?id=$userId\">$userId</a>",
            '{{userid}}' => (string)$userId,
        ]);
    }

    private function deleteMessage(Message $message): bool
    {
        $cacheKey = "Keyword::DELETE::{$message->getChat()->getId()}::{$message->getFrom()->getId()}::{$message->getMessageId()}";
        if (Cache::has($cacheKey)) {
            return false;
        }
        Cache::put($cacheKey, 1, Carbon::now()->addMinute());
        $this->dispatch(new DeleteMessageJob([
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
        ], 0));
        return true;
    }

    private function isProtected(Message $message, bool $includeAdmins = false): bool
    {
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        if ($userId === 777000) {
            return true;
        }
        if ($includeAdmins && in_array($userId, TChatAdmins::getChatAdmins($chatId), true)) {
            return true;
        }
        return in_array($userId, TChatKeywordsWhiteLists::getChatWhiteLists($chatId), true);
    }

    private function hasBlockingOperation(Message $message): bool
    {
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        $messageId = $message->getMessageId();

        return Cache::has("Keyword::WARN::$chatId::$userId") ||
            Cache::has("Keyword::RESTRICT::$chatId::$userId") ||
            Cache::has("Keyword::BAN::$chatId::$userId") ||
            Cache::has("Keyword::DELETE::$chatId::$userId::$messageId");
    }

    private function forward(array $data, Message $message): void
    {
        if ($this->hasBlockingOperation($message)) {
            return;
        }
        $cacheKey = "Keyword::FORWARD::{$message->getChat()->getId()}::{$message->getFrom()->getId()}::{$message->getMessageId()}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, 1, Carbon::now()->addMinute());

        $forwarder = [];

        isset($data['chat_id']) && $forwarder['chat_id'] = $data['chat_id'];

        if (isset($data['text'])) {
            $data['text'] = "Forwarded Message:\n\n" . $data['text'] . "\n\n";
        } else {
            $data['text'] = "Forwarded Message:\n\n";
        }
        $forwarder['text'] = $data['text'];

        $originalText = $message->getText() ?? $message->getCaption();
        if (mb_strlen($originalText, 'UTF-8') > 32) {
            $forwarder['text'] .= mb_substr($originalText, 0, 64, 'UTF-8') . '...' . "\n\n";
        } else {
            $forwarder['text'] .= $originalText . "\n\n";
        }

        $forwarder['text'] .= "Message ID: <code>{$message->getMessageId()}</code>\n";
        $forwarder['text'] .= "From Chat: <code>{$message->getChat()->getId()}</code>\n";
        $forwarder['text'] .= "From User: <a href='tg://user?id={$message->getFrom()->getId()}'>{$message->getFrom()->getId()}</a>\n";
        $cid = str_replace('-100', '', $message->getChat()->getId());
        $forwarder['text'] .= "Message Link: https://t.me/c/$cid/{$message->getMessageId()}";
        count($forwarder) == 2 && $this->dispatch(new SendMessageJob($forwarder, null, 0));
    }

    private function reply(array $data, Message $message): void
    {
        if ($this->hasBlockingOperation($message)) {
            return;
        }
        $cacheKey = "Keyword::REPLY::{$message->getChat()->getId()}::{$message->getFrom()->getId()}::{$message->getMessageId()}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, 1, Carbon::now()->addMinute());

        if (isset($data['text'])) {
            $sender = [
                'chat_id' => $message->getChat()->getId(),
                'reply_to_message_id' => $message->getMessageId(),
                'text' => $this->renderTemplate($data['text'], $message),
            ];
            if (isset($data['button'])) {
                $sender['reply_markup'] = new InlineKeyboard([]);
                foreach ($data['button'] as $row) {
                    $buttons = [];
                    foreach ($row as $button) {
                        $buttons[] = new InlineKeyboardButton([
                            'text' => $button['text'],
                            'url' => $button['url'],
                        ]);
                    }
                    $sender['reply_markup']->addRow(...$buttons);
                }
            }
            $this->dispatch(new SendMessageJob($sender, null, 0));
        }
    }
}
