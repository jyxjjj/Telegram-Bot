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

namespace App\Services\Commands;

use App\Jobs\SendMessageJob;
use App\Services\Base\BaseCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Entities\ReplyToMessage;
use Longman\TelegramBot\Telegram;

class MessageInfoCommand extends BaseCommand
{
    public string $name = 'messageinfo';
    public string $description = 'Show Message Info';
    public string $usage = '/messageinfo [reply_to] [text(default)|json]';

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
        $param = $message->getText(true);
        $data = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $messageId,
            'text' => '',
        ];

        $chatType = $message->getChat()->getType();
        if (!in_array($chatType, ['group', 'supergroup'], true)) {
            $data['text'] .= "<b>Error</b>: This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $replyTo = $message->getReplyToMessage();
        if (!$replyTo) {
            $data['text'] .= "<b>Error</b>: You should reply to a message for using this command.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $data['parse_mode'] = '';
        $param != 'json' && $data['text'] = $this->getMessageInfo($replyTo);
        $param == 'json' && $data['text'] = json_encode(array_merge($replyTo->getRawData()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->dispatch(new SendMessageJob($data));
    }

    private function getMessageInfo(ReplyToMessage $replyTo): string
    {
        $type = $replyTo->getType();
        $chat = $replyTo->getChat();
        $chatId = $chat->getId();
        $chatTitle = $chat->getTitle();
        $chatType = $chat->getType();
        $chatIsForum = $chat->getIsForum();
        $chatUsername = $chat->getUsername();
        $date = $replyTo->getDate();
        $forwardDate = $replyTo->getForwardDate();
        $forwardSenderName = $replyTo->getForwardSenderName();
        $forwardFrom = $replyTo->getForwardFrom();
        $forwardSignature = $replyTo->getForwardSignature();
        $forwardFromMessageId = $replyTo->getForwardFromMessageId();
        $forwardFromChat = $replyTo->getForwardFromChat();
        $forwardFromChat && $forwardFromChatId = $forwardFromChat->getId();
        $forwardFromChat && $forwardFromChatTitle = $forwardFromChat->getTitle();
        $forwardFromChat && $forwardFromChatType = $forwardFromChat->getType();
        $forwardFromChat && $forwardFromChatUsername = $forwardFromChat->getUsername();
        $from = $replyTo->getFrom();
        $fromName = ($from->getFirstName() ?? '') . ($from->getLastName() ?? '');
        $fromUserId = $from->getId();
        $fromIsBot = $from->getIsBot() ? 'true' : 'false';
        $fromUsername = $from->getUsername();
        $fromIsPremium = $from->getIsPremium() ? 'true' : 'false';
        $fromLanguageCode = $from->getLanguageCode();
        $isTopicMessage = $replyTo->getIsTopicMessage() ? 'true' : 'false';
        $messageThreadId = $replyTo->getMessageThreadId();
        $messageId = $replyTo->getMessageId();
        $text = $replyTo->getText();
        $caption = $replyTo->getCaption();
        $photo = $replyTo->getPhoto();
        $photo && usort($photo, function (PhotoSize $left, PhotoSize $right) {
            return bccomp(
                bcmul($right->getWidth(), $right->getHeight()),
                bcmul($left->getWidth(), $left->getHeight())
            );
        });
        $photo && $photoFileId = $photo[0]->getFileId();
        $photo && $photoFileUniqueId = $photo[0]->getFileUniqueId();
        $photo && $photoFileSize = $photo[0]->getFileSize();
        $dice = $replyTo->getDice();
        $dice && $diceEmoji = $dice->getEmoji();
        $dice && $diceValue = $dice->getValue();
        $audio = $replyTo->getAudio();
        $audio && $audioFileId = $audio->getFileId();
        $audio && $audioFileUniqueId = $audio->getFileUniqueId();
        $audio && $audioDuration = $audio->getDuration();
        $audio && $audioPerformer = $audio->getPerformer();
        $audio && $audioTitle = $audio->getTitle();
        $audio && $audioMimeType = $audio->getMimeType();
        $audio && $audioFileSize = $audio->getFileSize();
        $sticker = $replyTo->getSticker();
        $sticker && $stickerFileId = $sticker->getFileId();
        $sticker && $stickerFileUniqueId = $sticker->getFileUniqueId();
        $sticker && $stickerFileSize = $sticker->getFileSize();
        $sticker && $stickerIsAnimated = $sticker->getIsAnimated() ? 'true' : 'false';
        $sticker && $stickerIsVideo = $sticker->getIsVideo() ? 'true' : 'false';
        $sticker && $stickerSetName = $sticker->getSetName();
        $sticker && $stickerType = $sticker->getType();
        $sticker && $stickerEmoji = $sticker->getEmoji();
        $video = $replyTo->getVideo();
        $video && $videoFileId = $video->getFileId();
        $video && $videoFileUniqueId = $video->getFileUniqueId();
        $video && $videoDuration = $video->getDuration();
        $video && $videoMimeType = $video->getMimeType();
        $video && $videoFileSize = $video->getFileSize();
        $videoNote = $replyTo->getVideoNote();
        $videoNote && $videoNoteFileId = $videoNote->getFileId();
        $videoNote && $videoNoteFileUniqueId = $videoNote->getFileUniqueId();
        $videoNote && $videoNoteDuration = $videoNote->getDuration();
        $videoNote && $videoNoteLength = $videoNote->getLength();
        $videoNote && $videoNoteFileSize = $videoNote->getFileSize();
        $voice = $replyTo->getVoice();
        $voice && $voiceFileId = $voice->getFileId();
        $voice && $voiceFileUniqueId = $voice->getFileUniqueId();
        $voice && $voiceDuration = $voice->getDuration();
        $voice && $voiceMimeType = $voice->getMimeType();
        $voice && $voiceFileSize = $voice->getFileSize();
        $return[] = "Type: $type";
        $chatId && $return[] = "Chat ID: $chatId";
        $chatTitle && $return[] = "Chat Title: $chatTitle";
        $chatType && $return[] = "Chat Type: $chatType";
        $chatIsForum && $return[] = "Chat Is Forum: $chatIsForum";
        $chatUsername && $return[] = "Chat Username: $chatUsername";
        $return[] = "Date: $date";
        $forwardDate && $return[] = "Forward Date: $forwardDate";
        $forwardSenderName && $return[] = "Forward Sender Name: $forwardSenderName";
        $forwardFrom && $return[] = "Forward From: $forwardFrom";
        $forwardSignature && $return[] = "Forward Signature: $forwardSignature";
        $forwardFromMessageId && $return[] = "Forward From Message ID: $forwardFromMessageId";
        $forwardFromChat && isset($forwardFromChatId) && $return[] = "Forward From Chat ID: $forwardFromChatId";
        $forwardFromChat && isset($forwardFromChatTitle) && $return[] = "Forward From Chat Title: $forwardFromChatTitle";
        $forwardFromChat && isset($forwardFromChatType) && $return[] = "Forward From Chat Type: $forwardFromChatType";
        $forwardFromChat && isset($forwardFromChatUsername) && $return[] = "Forward From Chat Username: $forwardFromChatUsername";
        $return[] = "From Name: $fromName";
        $return[] = "From User ID: $fromUserId";
        $return[] = "From Is Bot: $fromIsBot";
        $fromUsername && $return[] = "From Username: $fromUsername";
        $return[] = "From Is Premium: $fromIsPremium";
        $fromLanguageCode && $return[] = "From Language Code: $fromLanguageCode";
        $return[] = "Is Topic Message: $isTopicMessage";
        $messageThreadId && $return[] = "Message Thread ID: $messageThreadId";
        $return[] = "Message ID: $messageId";
        $text && $return[] = "Text: $text";
        $caption && $return[] = "Caption: $caption";
        $photo && isset($photoFileId) && $return[] = "Photo File ID: $photoFileId";
        $photo && isset($photoFileUniqueId) && $return[] = "Photo File Unique ID: $photoFileUniqueId";
        $photo && isset($photoFileSize) && $return[] = "Photo File Size: $photoFileSize";
        $dice && isset($diceEmoji) && $return[] = "Dice Emoji: $diceEmoji";
        $dice && isset($diceValue) && $return[] = "Dice Value: $diceValue";
        $audio && isset($audioFileId) && $return[] = "Audio File ID: $audioFileId";
        $audio && isset($audioFileUniqueId) && $return[] = "Audio File Unique ID: $audioFileUniqueId";
        $audio && isset($audioDuration) && $return[] = "Audio Duration: $audioDuration";
        $audio && isset($audioPerformer) && $return[] = "Audio Performer: $audioPerformer";
        $audio && isset($audioTitle) && $return[] = "Audio Title: $audioTitle";
        $audio && isset($audioMimeType) && $return[] = "Audio MIME Type: $audioMimeType";
        $audio && isset($audioFileSize) && $return[] = "Audio File Size: $audioFileSize";
        $sticker && isset($stickerFileId) && $return[] = "Sticker File ID: $stickerFileId";
        $sticker && isset($stickerFileUniqueId) && $return[] = "Sticker File Unique ID: $stickerFileUniqueId";
        $sticker && isset($stickerFileSize) && $return[] = "Sticker File Size: $stickerFileSize";
        $sticker && isset($stickerIsAnimated) && $return[] = "Sticker Is Animated: $stickerIsAnimated";
        $sticker && isset($stickerIsVideo) && $return[] = "Sticker Is Video: $stickerIsVideo";
        $sticker && isset($stickerSetName) && $return[] = "Sticker Set Name: $stickerSetName";
        $sticker && isset($stickerType) && $return[] = "Sticker Type: $stickerType";
        $sticker && isset($stickerEmoji) && $return[] = "Sticker Emoji: $stickerEmoji";
        $video && isset($videoFileId) && $return[] = "Video File ID: $videoFileId";
        $video && isset($videoFileUniqueId) && $return[] = "Video File ID: $videoFileUniqueId";
        $video && isset($videoDuration) && $return[] = "Video Duration: $videoDuration";
        $video && isset($videoMimeType) && $return[] = "Video MIME Type: $videoMimeType";
        $video && isset($videoFileSize) && $return[] = "Video File Size: $videoFileSize";
        $videoNote && isset($videoNoteFileId) && $return[] = "Video Note File ID: $videoNoteFileId";
        $videoNote && isset($videoNoteFileUniqueId) && $return[] = "Video Note File Unique ID: $videoNoteFileUniqueId";
        $videoNote && isset($videoNoteDuration) && $return[] = "Video Note Duration: $videoNoteDuration";
        $videoNote && isset($videoNoteLength) && $return[] = "Video Note Length: $videoNoteLength";
        $videoNote && isset($videoNoteFileSize) && $return[] = "Video Note File Size: $videoNoteFileSize";
        $voice && isset($voiceFileId) && $return[] = "Voice File ID: $voiceFileId";
        $voice && isset($voiceFileUniqueId) && $return[] = "Voice File Unique ID: $voiceFileUniqueId";
        $voice && isset($voiceDuration) && $return[] = "Voice Duration: $voiceDuration";
        $voice && isset($voiceMimeType) && $return[] = "Voice MIME Type: $voiceMimeType";
        $voice && isset($voiceFileSize) && $return[] = "Voice File Size: $voiceFileSize";
        return implode("\n", $return);
    }
}
