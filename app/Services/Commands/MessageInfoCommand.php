<?php

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
            $data['text'] .= "*Error:* This command is available only for groups.\n";
            $this->dispatch(new SendMessageJob($data));
            return;
        }

        $replyTo = $message->getReplyToMessage();
        if (!$replyTo) {
            $data['text'] .= "*Error:* You should reply to a message for using this command.\n";
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
        $photo && usort($photo, function (PhotoSize $a, PhotoSize $b) {
            return $a->getFileSize() <=> $b->getFileSize();
        });
        $photo && $photoFileId = $photo[0]->getFileId();
        $photo && $photoFileSize = $photo[0]->getFileSize();
        $dice = $replyTo->getDice();
        $dice && $diceEmoji = $dice->getEmoji();
        $dice && $diceValue = $dice->getValue();
        $audio = $replyTo->getAudio();
        $audio && $audioFileId = $audio->getFileId();
        $audio && $audioDuration = $audio->getDuration();
        $audio && $audioPerformer = $audio->getPerformer();
        $audio && $audioTitle = $audio->getTitle();
        $audio && $audioMimeType = $audio->getMimeType();
        $audio && $audioFileSize = $audio->getFileSize();
        $sticker = $replyTo->getSticker();
        $sticker && $stickerFileId = $sticker->getFileId();
        $sticker && $stickerFileSize = $sticker->getFileSize();
        $sticker && $stickerIsAnimated = $sticker->getIsAnimated() ? 'true' : 'false';
        $sticker && $stickerIsVideo = $sticker->getIsVideo() ? 'true' : 'false';
        $sticker && $stickerSetName = $sticker->getSetName();
        $sticker && $stickerType = $sticker->getType();
        $sticker && $stickerEmoji = $sticker->getEmoji();
        $video = $replyTo->getVideo();
        $video && $videoFileId = $video->getFileId();
        $video && $videoDuration = $video->getDuration();
        $video && $videoMimeType = $video->getMimeType();
        $video && $videoFileSize = $video->getFileSize();
        $videoNote = $replyTo->getVideoNote();
        $videoNote && $videoNoteFileId = $videoNote->getFileId();
        $videoNote && $videoNoteDuration = $videoNote->getDuration();
        $videoNote && $videoNoteLength = $videoNote->getLength();
        $videoNote && $videoNoteFileSize = $videoNote->getFileSize();
        $voice = $replyTo->getVoice();
        $voice && $voiceFileId = $voice->getFileId();
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
        $photo && isset($photoFileSize) && $return[] = "Photo File Size: $photoFileSize";
        $dice && isset($diceEmoji) && $return[] = "Dice Emoji: $diceEmoji";
        $dice && isset($diceValue) && $return[] = "Dice Value: $diceValue";
        $audio && isset($audioFileId) && $return[] = "Audio File ID: $audioFileId";
        $audio && isset($audioDuration) && $return[] = "Audio Duration: $audioDuration";
        $audio && isset($audioPerformer) && $return[] = "Audio Performer: $audioPerformer";
        $audio && isset($audioTitle) && $return[] = "Audio Title: $audioTitle";
        $audio && isset($audioMimeType) && $return[] = "Audio MIME Type: $audioMimeType";
        $audio && isset($audioFileSize) && $return[] = "Audio File Size: $audioFileSize";
        $sticker && isset($stickerFileId) && $return[] = "Sticker File ID: $stickerFileId";
        $sticker && isset($stickerFileSize) && $return[] = "Sticker File Size: $stickerFileSize";
        $sticker && isset($stickerIsAnimated) && $return[] = "Sticker Is Animated: $stickerIsAnimated";
        $sticker && isset($stickerIsVideo) && $return[] = "Sticker Is Video: $stickerIsVideo";
        $sticker && isset($stickerSetName) && $return[] = "Sticker Set Name: $stickerSetName";
        $sticker && isset($stickerType) && $return[] = "Sticker Type: $stickerType";
        $sticker && isset($stickerEmoji) && $return[] = "Sticker Emoji: $stickerEmoji";
        $video && isset($videoFileId) && $return[] = "Video File ID: $videoFileId";
        $video && isset($videoDuration) && $return[] = "Video Duration: $videoDuration";
        $video && isset($videoMimeType) && $return[] = "Video MIME Type: $videoMimeType";
        $video && isset($videoFileSize) && $return[] = "Video File Size: $videoFileSize";
        $videoNote && isset($videoNoteFileId) && $return[] = "Video Note File ID: $videoNoteFileId";
        $videoNote && isset($videoNoteDuration) && $return[] = "Video Note Duration: $videoNoteDuration";
        $videoNote && isset($videoNoteLength) && $return[] = "Video Note Length: $videoNoteLength";
        $videoNote && isset($videoNoteFileSize) && $return[] = "Video Note File Size: $videoNoteFileSize";
        $voice && isset($voiceFileId) && $return[] = "Voice File ID: $voiceFileId";
        $voice && isset($voiceDuration) && $return[] = "Voice Duration: $voiceDuration";
        $voice && isset($voiceMimeType) && $return[] = "Voice MIME Type: $voiceMimeType";
        $voice && isset($voiceFileSize) && $return[] = "Voice File Size: $voiceFileSize";
        return implode("\n", $return);
    }
}
